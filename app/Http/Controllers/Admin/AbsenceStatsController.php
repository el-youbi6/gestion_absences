<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absence;
use App\Models\AnneeScolaire;
use App\Services\AcademicYearService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AbsenceStatsController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeAdmin($request);

        $annee = AcademicYearService::getSessionAcademicYear();
        $anneeScolaireId = $annee?->id;

        $baseQuery = Absence::query()
            ->when($anneeScolaireId, fn ($query) => $query->where('annee_scolaire_id', $anneeScolaireId));

        $totalAbsences = (clone $baseQuery)->count();
        $todayAbsences = (clone $baseQuery)->whereDate('date', now()->toDateString())->count();
        $justifiedAbsences = (clone $baseQuery)->where('is_justified', true)->count();
        $unjustifiedAbsences = (clone $baseQuery)->where('is_justified', false)->count();

        $statsByFiliere = Absence::query()
            ->select([
                'filieres.id',
                'filieres.nom',
                DB::raw('COUNT(absences.id) as total_absences'),
                DB::raw('SUM(CASE WHEN absences.is_justified = 1 THEN 1 ELSE 0 END) as justified_absences'),
                DB::raw('SUM(CASE WHEN absences.is_justified = 0 THEN 1 ELSE 0 END) as unjustified_absences'),
            ])
            ->join('stagiaires', 'stagiaires.id', '=', 'absences.stagiaire_id')
            ->join('groupes', 'groupes.id', '=', 'stagiaires.groupe_id')
            ->join('filieres', 'filieres.id', '=', 'groupes.filiere_id')
            ->when($anneeScolaireId, fn ($query) => $query->where('absences.annee_scolaire_id', $anneeScolaireId))
            ->groupBy('filieres.id', 'filieres.nom')
            ->orderByDesc('total_absences')
            ->get()
            ->map(fn ($filiere) => [
                'id' => $filiere->id,
                'nom' => $filiere->nom,
                'total_absences' => (int) $filiere->total_absences,
                'justified_absences' => (int) $filiere->justified_absences,
                'unjustified_absences' => (int) $filiere->unjustified_absences,
            ]);

        $topStagiaires = Absence::query()
            ->select([
                'stagiaires.id',
                'users.nom',
                'users.prenom',
                'users.cin',
                'groupes.nom as groupe',
                'filieres.nom as filiere',
                DB::raw('COUNT(absences.id) as total_absences'),
                DB::raw('MAX(absences.date) as last_absence_date'),
            ])
            ->join('stagiaires', 'stagiaires.id', '=', 'absences.stagiaire_id')
            ->join('users', 'users.id', '=', 'stagiaires.user_id')
            ->leftJoin('groupes', 'groupes.id', '=', 'stagiaires.groupe_id')
            ->leftJoin('filieres', 'filieres.id', '=', 'groupes.filiere_id')
            ->when($anneeScolaireId, fn ($query) => $query->where('absences.annee_scolaire_id', $anneeScolaireId))
            ->groupBy('stagiaires.id', 'users.nom', 'users.prenom', 'users.cin', 'groupes.nom', 'filieres.nom')
            ->orderByDesc('total_absences')
            ->limit(10)
            ->get()
            ->map(fn ($stagiaire) => [
                'id' => $stagiaire->id,
                'nom' => trim($stagiaire->nom . ' ' . $stagiaire->prenom),
                'cin' => $stagiaire->cin,
                'groupe' => $stagiaire->groupe,
                'filiere' => $stagiaire->filiere,
                'total_absences' => (int) $stagiaire->total_absences,
                'last_absence_date' => $stagiaire->last_absence_date,
            ]);

        return Inertia::render('admin/AbsenceStats', [
            'annee' => $annee ?: AnneeScolaire::query()->latest('id')->first(),
            'summary' => [
                'total_absences' => $totalAbsences,
                'today_absences' => $todayAbsences,
                'justified_absences' => $justifiedAbsences,
                'unjustified_absences' => $unjustifiedAbsences,
            ],
            'statsByFiliere' => $statsByFiliere,
            'topStagiaires' => $topStagiaires,
        ]);
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless(in_array($request->user()?->role, ['admin', 'surveillant'], true), 403);
    }
}
