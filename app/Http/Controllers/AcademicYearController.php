<?php

namespace App\Http\Controllers;

use App\Services\AcademicYearService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AcademicYearController extends Controller
{
    /**
     * Change l'année scolaire active
     */
    public function setActive(Request $request): RedirectResponse
    {
        $request->validate([
            'annee_scolaire_id' => 'required|exists:annee_scolaires,id'
        ]);

        AcademicYearService::setActiveAcademicYear($request->annee_scolaire_id);

        return back()->with('success', 'Année scolaire modifiée avec succès');
    }

    /**
     * Retourne toutes les années scolaires au format JSON
     * Utile pour les sélecteurs côté client
     */
    public function getAll()
    {
        $annees = AcademicYearService::getAllAcademicYears();
        $current = AcademicYearService::getSessionAcademicYearId();

        return response()->json([
            'annees' => $annees,
            'current' => $current
        ]);
    }
}
