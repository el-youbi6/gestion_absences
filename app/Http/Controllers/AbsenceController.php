<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\Module;
use App\Services\AcademicYearService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AbsenceController extends Controller
{
    public function create(Request $request)
    {
        $formateur = $this->getFormateur($request);
        $anneeScolaireId = AcademicYearService::getSessionAcademicYearId();

        if (! $formateur) {
            return redirect('/import')->with('error', 'Aucun profil formateur associe a ce compte.');
        }

        $formateur->load([
            'modules.filiere',
            'groupes.filiere',
            'groupes.stagiaires.user',
        ]);

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

        $anneeScolaireId = AcademicYearService::getSessionAcademicYearId();
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
        $annee = AcademicYearService::getSessionAcademicYear();
        $anneeScolaireId = $annee?->id;

        if (! $formateur) {
            return redirect('/import')->with('error', 'Aucun profil formateur associe a ce compte.');
        }

        $modules = $formateur->modules()
            ->with('filiere')
            ->when($anneeScolaireId, fn ($query) => $query->where('formateur_module.annee_scolaire_id', $anneeScolaireId))
            ->orderBy('nom')
            ->get()
            ->map(fn ($module) => [
                'id' => $module->id,
                'nom' => $module->nom,
                'filiere' => $module->filiere?->nom,
            ]);

        $selectedModuleId = (int) ($request->query('module_id') ?: ($modules->first()['id'] ?? 0));
        $canUseModule = $selectedModuleId
            ? $formateur->modules()
                ->whereKey($selectedModuleId)
                ->when($anneeScolaireId, fn ($query) => $query->where('formateur_module.annee_scolaire_id', $anneeScolaireId))
                ->exists()
            : false;

        if (! $canUseModule) {
            $selectedModuleId = 0;
        }

        $selectedModule = $selectedModuleId
            ? Module::with('filiere')->find($selectedModuleId)
            : null;

        $baseQuery = Absence::query()
            ->when($selectedModuleId, fn ($query) => $query->where('module_id', $selectedModuleId))
            ->when($anneeScolaireId, fn ($query) => $query->where('annee_scolaire_id', $anneeScolaireId));

        $summary = [
            'total_absences' => (clone $baseQuery)->count(),
            'today_absences' => (clone $baseQuery)->whereDate('date', now()->toDateString())->count(),
            'justified_absences' => (clone $baseQuery)->where('is_justified', true)->count(),
            'unjustified_absences' => (clone $baseQuery)->where('is_justified', false)->count(),
        ];

        $stagiaires = Absence::query()
            ->select([
                'stagiaires.id',
                'users.nom',
                'users.prenom',
                'users.cin',
                'groupes.nom as groupe',
                DB::raw('COUNT(absences.id) as total_absences'),
                DB::raw('SUM(CASE WHEN absences.is_justified = 1 THEN 1 ELSE 0 END) as justified_absences'),
                DB::raw('SUM(CASE WHEN absences.is_justified = 0 THEN 1 ELSE 0 END) as unjustified_absences'),
                DB::raw('MAX(absences.date) as last_absence_date'),
            ])
            ->join('stagiaires', 'stagiaires.id', '=', 'absences.stagiaire_id')
            ->join('users', 'users.id', '=', 'stagiaires.user_id')
            ->leftJoin('groupes', 'groupes.id', '=', 'stagiaires.groupe_id')
            ->where('absences.module_id', $selectedModuleId)
            ->when($anneeScolaireId, fn ($query) => $query->where('absences.annee_scolaire_id', $anneeScolaireId))
            ->groupBy('stagiaires.id', 'users.nom', 'users.prenom', 'users.cin', 'groupes.nom')
            ->orderByDesc('total_absences')
            ->get()
            ->map(fn ($stagiaire) => [
                'id' => $stagiaire->id,
                'nom' => trim($stagiaire->nom . ' ' . $stagiaire->prenom),
                'cin' => $stagiaire->cin,
                'groupe' => $stagiaire->groupe,
                'total_absences' => (int) $stagiaire->total_absences,
                'justified_absences' => (int) $stagiaire->justified_absences,
                'unjustified_absences' => (int) $stagiaire->unjustified_absences,
                'last_absence_date' => $stagiaire->last_absence_date,
            ]);

        $recentAbsences = Absence::query()
            ->with(['stagiaire.user', 'stagiaire.groupe'])
            ->where('module_id', $selectedModuleId)
            ->when($anneeScolaireId, fn ($query) => $query->where('annee_scolaire_id', $anneeScolaireId))
            ->orderByDesc('date')
            ->orderByDesc('heure_debut')
            ->limit(12)
            ->get()
            ->map(fn ($absence) => [
                'id' => $absence->id,
                'stagiaire' => trim(($absence->stagiaire?->user?->nom ?? '') . ' ' . ($absence->stagiaire?->user?->prenom ?? '')),
                'cin' => $absence->stagiaire?->user?->cin,
                'groupe' => $absence->stagiaire?->groupe?->nom,
                'date' => $absence->date,
                'heure_debut' => substr($absence->heure_debut, 0, 5),
                'heure_fin' => substr($absence->heure_fin, 0, 5),
                'is_justified' => $absence->is_justified,
            ]);

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

        foreach ($formateur->groupes as $groupe) {
            if ($groupe->filiere) {
                $filieres[$groupe->filiere->id] = $groupe->filiere;
            }
        }

        foreach ($formateur->modules as $module) {
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
        $modules = $formateur->modules->sortBy('nom');
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
        $groupes = $formateur->groupes->sortBy('nom');
        $result = [];

        foreach ($groupes as $groupe) {
            $stagiaires = [];

            foreach ($groupe->stagiaires->sortBy('user.nom') as $stagiaire) {
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
}
