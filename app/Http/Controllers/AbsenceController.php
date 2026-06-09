<?php

namespace App\Http\Controllers;

use App\Models\Absence;
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

}
