<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\Module;
use App\Services\YearService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RapportController extends Controller
{
    public function index(Request $request)
    {
        $formateur = $request->user()?->formateur;

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
