<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Groupe;
use App\Models\Formateur;
use App\Services\AcademicYearService;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class FormateurGroupeImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return void
     */
    public function model(array $row)
    {
        // Valider les données requises
        if (empty($row['formateur_cin']) || empty($row['groupe'])) {
            return;
        }

        // Récupérer le formateur par CIN
        $user = User::where('cin', $row['formateur_cin'])->first();
        if (!$user) {
            return;
        }

        // Récupérer le groupe lié à l'année scolaire actuelle
        $anneeId = AcademicYearService::getSessionAcademicYearId();
        if (!$anneeId) {
            return;
        }

        $groupe = Groupe::where('nom', $row['groupe'])
            ->where('annee_scolaire_id', $anneeId)
            ->first();

        if (!$groupe) {
            return;
        }

        // Récupérer le formateur
        $formateur = Formateur::where('user_id', $user->id)->first();
        if (!$formateur) {
            return;
        }

        // Associer le formateur au groupe pour l'annee active.
        DB::table('formateur_groupe')->updateOrInsert(
            [
                'formateur_id' => $formateur->id,
                'groupe_id' => $groupe->id,
                'annee_scolaire_id' => $anneeId,
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
