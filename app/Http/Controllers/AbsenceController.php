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

        $anneeScolaireId = YearService::getSessionYearId();

        Absence::where('module_id', $request->module_id)
            ->where('date', $request->date)
            ->where('heure_debut', $request->heure_debut)
            ->where('heure_fin', $request->heure_fin)
            ->delete();

        if ($request->presences) {

            foreach ($request->presences as $presence) {

                $absence = new Absence();

                $absence->stagiaire_id = $presence['stagiaire_id'];
                $absence->module_id = $request->module_id;
                $absence->annee_scolaire_id = $anneeScolaireId;
                $absence->date = $request->date;
                $absence->heure_debut = $request->heure_debut;
                $absence->heure_fin = $request->heure_fin;
                $absence->is_justified = 0;

                $absence->save();
            }
        }

        return back()->with('success', 'Absences ajoutees avec succes');
    }

    public function moduleReport(Request $request)
    {
        $formateur = $this->getFormateur($request);

        $annee = YearService::getSessionYear();
        $anneeScolaireId = $annee->id;

        $modulesData = $formateur->modules()->with('filiere')->get();

        $modules = [];

        foreach ($modulesData as $module) {

            $modules[] = [
                'id' => $module->id,
                'nom' => $module->nom,
                'filiere' => $module->filiere->nom,
            ];
        }

        $selectModuleId = $request->module_id;

        if (!$selectModuleId && count($modules) > 0) {
            $selectModuleId = $modules[0]['id'];
        }

        $selectModule = Module::with('filiere')->find($selectModuleId);

        $selectModule = $selectModule ? [
            'id' => $selectModule->id,
            'nom' => $selectModule->nom,
            'filiere' => $selectModule->filiere?->nom,
            'filiere_id' => $selectModule->filiere_id,
        ] : null;

        $summary = [
            'total_absences' => $this->countModuleAbsences($selectModuleId, $anneeScolaireId),
            'today_absences' => $this->countModuleAbsences($selectModuleId, $anneeScolaireId, now()->toDateString()),
            'justified_absences' => $this->countModuleAbsences($selectModuleId, $anneeScolaireId, null, true),
            'unjustified_absences' => $this->countModuleAbsences($selectModuleId, $anneeScolaireId, null, false),
        ];

        $stagiaires = $this->prepareStagiaires($selectModuleId, $anneeScolaireId);

        $absences = Absence::with(['stagiaire.user', 'stagiaire.groupe'])
            ->where('module_id', $selectModuleId)
            ->where('annee_scolaire_id', $anneeScolaireId)
            ->latest('date')
            ->take(12)
            ->get();

        $recentAbsences = [];

        foreach ($absences as $absence) {

            $recentAbsences[] = [
                'id' => $absence->id,
                'stagiaire' => $absence->stagiaire->user->nom . ' ' . $absence->stagiaire->user->prenom,
                'cin' => $absence->stagiaire->user->cin,
                'groupe' => $absence->stagiaire->groupe->nom,
                'date' => $absence->date,
                'heure_debut' => substr($absence->heure_debut, 0, 5),
                'heure_fin' => substr($absence->heure_fin, 0, 5),
                'is_justified' => $absence->is_justified,
            ];
        }

        return Inertia::render('formateur/RapportModule', [
            'annee' => $annee,
            'modules' => $modules,
            'selectModuleId' => $selectModuleId,
            'selectModule' => $selectModule,
            'summary' => $summary,
            'stagiaires' => $stagiaires,
            'recentAbsences' => $recentAbsences,
        ]);
    }

    public function getFormateur(Request $request)
    {
        return $request->user()?->formateur;
    }

    public function authorizeFormateur(Request $request): void
    {
        abort_unless($request->user()?->role === 'formateur', 403);
    }

    public function prepareFilieres($formateur)
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

        $result = [];

        foreach ($filieres as $filiere) {
            $result[] = [
                'id' => $filiere->id,
                'nom' => $filiere->nom,
            ];
        }

        return $result;
    }

    public function prepareModules($formateur)
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

    public function prepareGroupes($formateur, int $anneeScolaireId)
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

    public function getLatestAbsence(int $stagiaireId, int $anneeScolaireId)
    {
        return Absence::where('stagiaire_id', $stagiaireId)
            ->where('annee_scolaire_id', $anneeScolaireId)
            ->orderByDesc('date')
            ->orderByDesc('heure_debut')
            ->orderByDesc('id')
            ->first();
    }

    public function countModuleAbsences(int $moduleId, ?int $anneeScolaireId, ?string $date = null, ?bool $isJustified = null): int
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

    public function prepareStagiaires(int $moduleId, ?int $anneeScolaireId): array
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
