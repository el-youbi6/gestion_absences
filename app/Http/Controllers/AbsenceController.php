<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\Module;
use App\Services\YearService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AbsenceController extends Controller
{
    public function create(Request $request)
    {
        $formateur = $this->getFormateur($request);
        $anneeScolaireId = YearService::getSessionYearId();

        if (! $formateur) {
            return redirect('/import')->with('error', 'Aucun profil formateur associe a ce compte.');
        }

        $filieres = $this->prepareFilieres($formateur);
        $modules = $this->prepareModules($formateur);
        $groupes = $this->prepareGroupes($formateur, $anneeScolaireId);

        return Inertia::render('formateur/Saisie', [
            'filieres' => $filieres,
            'modules' => $modules,
            'groupes' => $groupes,
            'timeSlots' => [
                'debut' => [
                    ['label' => '8:30', 'value' => '08:30'],
                    ['label' => '11:00', 'value' => '11:00'],
                    ['label' => '13:30', 'value' => '13:30'],
                    ['label' => '16:00', 'value' => '16:00'],
                ],
                'fin' => [
                    ['label' => '11:00', 'value' => '11:00'],
                    ['label' => '13:30', 'value' => '13:30'],
                    ['label' => '16:00', 'value' => '16:00'],
                    ['label' => '18:30', 'value' => '18:30'],
                ],
            ],
            'today' => now()->toDateString(),
        ]);
    }

    public function store(Request $request)
    {
        $formateur = $this->getFormateur($request);

        if (! $formateur) {
            return back()->with('error', 'Aucun profil formateur associe a ce compte.');
        }

        $validated = $request->validate([
            'filiere_id' => ['required', 'exists:filieres,id'],
            'module_id' => ['required', 'exists:modules,id'],
            'groupe_id' => ['required', 'exists:groupes,id'],
            'date' => ['required', 'date'],
            'heure_debut' => ['required', 'date_format:H:i'],
            'heure_fin' => ['required', 'date_format:H:i', 'after:heure_debut'],
            'presences' => ['nullable', 'array'],
            'presences.*.stagiaire_id' => ['required', 'exists:stagiaires,id'],
        ]);

        $canUseModule = $formateur->modules()
            ->whereKey($validated['module_id'])
            ->where('filiere_id', $validated['filiere_id'])
            ->exists();

        $canUseGroupe = $formateur->groupes()
            ->whereKey($validated['groupe_id'])
            ->where('filiere_id', $validated['filiere_id'])
            ->exists();

        if (! $canUseModule || ! $canUseGroupe) {
            return back()->with('error', 'Module ou groupe non autorise pour ce formateur.');
        }

        $groupe = $formateur->groupes()
            ->whereKey($validated['groupe_id'])
            ->first();

        if (! $groupe) {
            return back()->with('error', 'Groupe introuvable pour ce formateur.');
        }

        $allowedStagiaireIds = $groupe->stagiaires()
            ->pluck('id')
            ->all();

        $anneeScolaireId = YearService::getSessionYearId();
        $absentStagiaireIds = [];

        foreach ($validated['presences'] ?? [] as $presence) {
            $stagiaireId = (int) $presence['stagiaire_id'];

            if (in_array($stagiaireId, $allowedStagiaireIds, true)) {
                $absentStagiaireIds[] = $stagiaireId;
            }
        }

        $sessionAbsences = Absence::query()
            ->where('module_id', $validated['module_id'])
            ->where('annee_scolaire_id', $anneeScolaireId)
            ->where('date', $validated['date'])
            ->where('heure_debut', $validated['heure_debut'])
            ->where('heure_fin', $validated['heure_fin'])
            ->whereIn('stagiaire_id', $allowedStagiaireIds)
            ->where('is_justified', false);

        if ($absentStagiaireIds === []) {
            $sessionAbsences->delete();
        } else {
            $sessionAbsences
                ->whereNotIn('stagiaire_id', $absentStagiaireIds)
                ->delete();
        }

        foreach ($validated['presences'] ?? [] as $presence) {
            if (! in_array((int) $presence['stagiaire_id'], $allowedStagiaireIds, true)) {
                continue;
            }

            Absence::firstOrCreate(
                [
                    'stagiaire_id' => $presence['stagiaire_id'],
                    'module_id' => $validated['module_id'],
                    'date' => $validated['date'],
                    'heure_debut' => $validated['heure_debut'],
                    'heure_fin' => $validated['heure_fin'],
                ],
                [
                    'annee_scolaire_id' => $anneeScolaireId,
                    'is_justified' => false,
                ]
            );
        }

        return back()->with('success', 'Absences enregistrees avec succes.');
    }

    public function moduleReport(Request $request)
    {
        $this->authorizeFormateur($request);

        $formateur = $this->getFormateur($request);
        $annee = YearService::getSessionYear();
        $anneeScolaireId = $annee?->id;

        if (! $formateur) {
            return redirect('/import')->with('error', 'Aucun profil formateur associe a ce compte.');
        }

        $modulesQuery = $formateur->modules()
            ->with('filiere')
            ->orderBy('nom');

        if ($anneeScolaireId) {
            $modulesQuery->where('formateur_module.annee_scolaire_id', $anneeScolaireId);
        }

        $modules = [];

        foreach ($modulesQuery->get() as $module) {
            $modules[] = [
                'id' => $module->id,
                'nom' => $module->nom,
                'filiere' => $module->filiere?->nom,
            ];
        }

        $selectedModuleId = (int) $request->query('module_id');

        if (! $selectedModuleId && count($modules) > 0) {
            $selectedModuleId = $modules[0]['id'];
        }

        $canUseModule = false;

        if ($selectedModuleId) {
            $moduleQuery = $formateur->modules()->where('modules.id', $selectedModuleId);

            if ($anneeScolaireId) {
                $moduleQuery->where('formateur_module.annee_scolaire_id', $anneeScolaireId);
            }

            $canUseModule = $moduleQuery->exists();
        }

        if (! $canUseModule) {
            $selectedModuleId = 0;
        }

        $selectedModule = $selectedModuleId
            ? Module::with('filiere')->find($selectedModuleId)
            : null;

        $summary = [
            'total_absences' => $this->countModuleAbsences($selectedModuleId, $anneeScolaireId),
            'today_absences' => $this->countModuleAbsences($selectedModuleId, $anneeScolaireId, now()->toDateString()),
            'justified_absences' => $this->countModuleAbsences($selectedModuleId, $anneeScolaireId, null, true),
            'unjustified_absences' => $this->countModuleAbsences($selectedModuleId, $anneeScolaireId, null, false),
        ];

        $stagiaires = $this->prepareStagiairesReport($selectedModuleId, $anneeScolaireId);

        $recentAbsencesQuery = Absence::query()
            ->with(['stagiaire.user', 'stagiaire.groupe'])
            ->where('module_id', $selectedModuleId)
            ->orderByDesc('date')
            ->orderByDesc('heure_debut')
            ->limit(12);

        if ($anneeScolaireId) {
            $recentAbsencesQuery->where('annee_scolaire_id', $anneeScolaireId);
        }

        $recentAbsences = [];

        foreach ($recentAbsencesQuery->get() as $absence) {
            $recentAbsences[] = [
                'id' => $absence->id,
                'stagiaire' => trim(($absence->stagiaire?->user?->nom ?? '') . ' ' . ($absence->stagiaire?->user?->prenom ?? '')),
                'cin' => $absence->stagiaire?->user?->cin,
                'groupe' => $absence->stagiaire?->groupe?->nom,
                'date' => $absence->date,
                'heure_debut' => substr($absence->heure_debut, 0, 5),
                'heure_fin' => substr($absence->heure_fin, 0, 5),
                'is_justified' => $absence->is_justified,
            ];
        }

        return Inertia::render('formateur/RapportModule', [
            'annee' => $annee,
            'modules' => $modules,
            'selectedModuleId' => $selectedModuleId,
            'selectedModule' => $selectedModule ? [
                'id' => $selectedModule->id,
                'nom' => $selectedModule->nom,
                'filiere' => $selectedModule->filiere?->nom,
            ] : null,
            'summary' => $summary,
            'stagiaires' => $stagiaires,
            'recentAbsences' => $recentAbsences,
        ]);
    }

    private function getFormateur(Request $request)
    {
        return $request->user()?->formateur;
    }

    private function authorizeFormateur(Request $request): void
    {
        abort_unless($request->user()?->role === 'formateur', 403);
    }

    private function prepareFilieres($formateur)
    {
        $filieres = [];

        $groupes = $formateur->groupes()->with('filiere')->get();
        $modules = $formateur->modules()->with('filiere')->get();

        foreach ($groupes as $groupe) {
            if ($groupe->filiere) {
                $filieres[$groupe->filiere->id] = $groupe->filiere;
            }
        }

        foreach ($modules as $module) {
            if ($module->filiere) {
                $filieres[$module->filiere->id] = $module->filiere;
            }
        }

        usort($filieres, function ($firstFiliere, $secondFiliere) {
            return strcmp($firstFiliere->nom, $secondFiliere->nom);
        });

        $result = [];

        foreach ($filieres as $filiere) {
            $result[] = [
                'id' => $filiere->id,
                'nom' => $filiere->nom,
            ];
        }

        return $result;
    }

    private function prepareModules($formateur)
    {
        $modules = $formateur->modules()
            ->orderBy('nom')
            ->get();

        $result = [];

        foreach ($modules as $module) {
            $result[] = [
                'id' => $module->id,
                'nom' => $module->nom,
                'filiere_id' => $module->filiere_id,
            ];
        }

        return $result;
    }

    private function prepareGroupes($formateur, int $anneeScolaireId)
    {
        $groupes = $formateur->groupes()
            ->with(['stagiaires.user'])
            ->orderBy('nom')
            ->get();

        $result = [];

        foreach ($groupes as $groupe) {
            $stagiaires = [];

            $stagiairesCollection = $groupe->stagiaires->sortBy('user.nom');

            foreach ($stagiairesCollection as $stagiaire) {
                $latestAbsence = $this->getLatestAbsence($stagiaire->id, $anneeScolaireId);

                $stagiaires[] = [
                    'id' => $stagiaire->id,
                    'nom' => $stagiaire->user?->nom,
                    'prenom' => $stagiaire->user?->prenom,
                    'cin' => $stagiaire->user?->cin,
                    'latest_absence' => $latestAbsence ? [
                        'id' => $latestAbsence->id,
                        'is_justified' => $latestAbsence->is_justified,
                    ] : null,
                ];
            }

            $result[] = [
                'id' => $groupe->id,
                'nom' => $groupe->nom,
                'filiere_id' => $groupe->filiere_id,
                'stagiaires' => $stagiaires,
            ];
        }

        return $result;
    }

    private function getLatestAbsence(int $stagiaireId, int $anneeScolaireId)
    {
        return Absence::where('stagiaire_id', $stagiaireId)
            ->where('annee_scolaire_id', $anneeScolaireId)
            ->orderByDesc('date')
            ->orderByDesc('heure_debut')
            ->orderByDesc('id')
            ->first();
    }

    private function countModuleAbsences(int $moduleId, ?int $anneeScolaireId, ?string $date = null, ?bool $isJustified = null): int
    {
        if (! $moduleId) {
            return 0;
        }

        $query = Absence::where('module_id', $moduleId);

        if ($anneeScolaireId) {
            $query->where('annee_scolaire_id', $anneeScolaireId);
        }

        if ($date) {
            $query->whereDate('date', $date);
        }

        if ($isJustified !== null) {
            $query->where('is_justified', $isJustified);
        }

        return $query->count();
    }

    private function prepareStagiairesReport(int $moduleId, ?int $anneeScolaireId): array
    {
        if (! $moduleId) {
            return [];
        }

        $query = Absence::with(['stagiaire.user', 'stagiaire.groupe'])
            ->where('module_id', $moduleId)
            ->orderByDesc('date');

        if ($anneeScolaireId) {
            $query->where('annee_scolaire_id', $anneeScolaireId);
        }

        $stagiaires = [];

        foreach ($query->get() as $absence) {
            if (! $absence->stagiaire || ! $absence->stagiaire->user) {
                continue;
            }

            $stagiaireId = $absence->stagiaire->id;

            if (! isset($stagiaires[$stagiaireId])) {
                $stagiaires[$stagiaireId] = [
                    'id' => $stagiaireId,
                    'nom' => trim($absence->stagiaire->user->nom . ' ' . $absence->stagiaire->user->prenom),
                    'cin' => $absence->stagiaire->user->cin,
                    'groupe' => $absence->stagiaire->groupe?->nom,
                    'total_absences' => 0,
                    'justified_absences' => 0,
                    'unjustified_absences' => 0,
                    'last_absence_date' => $absence->date,
                ];
            }

            $stagiaires[$stagiaireId]['total_absences']++;

            if ($absence->is_justified) {
                $stagiaires[$stagiaireId]['justified_absences']++;
            } else {
                $stagiaires[$stagiaireId]['unjustified_absences']++;
            }

            if ($absence->date > $stagiaires[$stagiaireId]['last_absence_date']) {
                $stagiaires[$stagiaireId]['last_absence_date'] = $absence->date;
            }
        }

        $stagiaires = array_values($stagiaires);

        usort($stagiaires, function ($firstStagiaire, $secondStagiaire) {
            return $secondStagiaire['total_absences'] <=> $firstStagiaire['total_absences'];
        });

        return $stagiaires;
    }
}
