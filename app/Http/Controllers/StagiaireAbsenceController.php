<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use Illuminate\Http\Request;

class StagiaireAbsenceController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()?->role === 'stagiaire', 403);

        $stagiaire = $request->user()->stagiaire;

        if (! $stagiaire) {
            return redirect('/login')->with('error', 'Aucun profil stagiaire associe a ce compte.');
        }

        $totalAbsences = Absence::where('stagiaire_id', $stagiaire->id)->count();

        $absencesJustifiees = Absence::where('stagiaire_id', $stagiaire->id)
            ->where('is_justified', true)
            ->count();

        $absencesNonJustifiees = Absence::where('stagiaire_id', $stagiaire->id)
            ->where('is_justified', false)
            ->count();

        $absenceAujourdhui = Absence::where('stagiaire_id', $stagiaire->id)
            ->whereDate('date', now()->toDateString())
            ->exists();

        $absences = Absence::with('module')
            ->where('stagiaire_id', $stagiaire->id)
            ->orderByDesc('date')
            ->orderByDesc('heure_debut')
            ->paginate(10);

        return view('stagiaire.suivi-absences', [
            'stagiaire' => $stagiaire,
            'totalAbsences' => $totalAbsences,
            'absencesJustifiees' => $absencesJustifiees,
            'absencesNonJustifiees' => $absencesNonJustifiees,
            'absenceAujourdhui' => $absenceAujourdhui,
            'absences' => $absences,
        ]);
    }
}
