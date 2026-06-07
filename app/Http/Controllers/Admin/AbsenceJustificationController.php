<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\Stagiaire;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AbsenceJustificationController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'cin' => ['nullable', 'string'],
            'date_debut' => ['nullable', 'date'],
            'date_fin' => ['nullable', 'date', 'after_or_equal:date_debut'],
        ]);

        $cin = $validated['cin'] ?? null;
        $dateDebut = $validated['date_debut'] ?? null;
        $dateFin = $validated['date_fin'] ?? null;

        $stagiaire = null;
        $absences = [];

        if ($cin) {
            $stagiaire = Stagiaire::with(['user', 'groupe.filiere'])
                ->whereHas('user', function ($query) use ($cin) {
                    $query->where('cin', $cin);
                })
                ->first();

            if ($stagiaire) {
                $query = Absence::with('module')
                    ->where('stagiaire_id', $stagiaire->id)
                    ->orderByDesc('date')
                    ->orderByDesc('heure_debut');

                if ($dateDebut) {
                    $query->whereDate('date', '>=', $dateDebut);
                }

                if ($dateFin) {
                    $query->whereDate('date', '<=', $dateFin);
                }

                $absences = $query->get()->map(function ($absence) {
                    return [
                        'id' => $absence->id,
                        'module' => $absence->module?->nom,
                        'date' => $absence->date,
                        'heure_debut' => substr($absence->heure_debut, 0, 5),
                        'heure_fin' => substr($absence->heure_fin, 0, 5),
                        'is_justified' => $absence->is_justified,
                    ];
                });

                $stagiaire = [
                    'id' => $stagiaire->id,
                    'nom' => $stagiaire->user?->nom,
                    'prenom' => $stagiaire->user?->prenom,
                    'cin' => $stagiaire->user?->cin,
                    'email' => $stagiaire->user?->email,
                    'groupe' => $stagiaire->groupe?->nom,
                    'filiere' => $stagiaire->groupe?->filiere?->nom,
                ];
            }
        }

        return Inertia::render('admin/AbsenceJustification', [
            'filters' => [
                'cin' => $cin,
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin,
            ],
            'stagiaire' => $stagiaire,
            'absences' => $absences,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'stagiaire_id' => ['required', 'exists:stagiaires,id'],
            'absence_ids' => ['required', 'array', 'min:1'],
            'absence_ids.*' => ['required', 'exists:absences,id'],
            'cin' => ['nullable', 'string'],
            'date_debut' => ['nullable', 'date'],
            'date_fin' => ['nullable', 'date', 'after_or_equal:date_debut'],
        ]);

        $updatedAbsences = Absence::where('stagiaire_id', $validated['stagiaire_id'])
            ->whereIn('id', $validated['absence_ids'])
            ->update(['is_justified' => true]);

        return redirect()
            ->route('admin.absence-justification.index', [
                'cin' => $validated['cin'] ?? null,
                'date_debut' => $validated['date_debut'] ?? null,
                'date_fin' => $validated['date_fin'] ?? null,
            ])
            ->with('success', $updatedAbsences . ' absence(s) justifiee(s) avec succes.');
    }
}
