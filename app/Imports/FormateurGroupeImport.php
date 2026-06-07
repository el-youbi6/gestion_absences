<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Groupe;
use App\Models\Formateur;
use App\Services\YearService;
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
        if (empty($row['formateur_cin']) || empty($row['groupe'])) {
            return;
        }

        $user = User::where('cin', $row['formateur_cin'])->first();
        if (!$user) {
            return;
        }

        $anneeId = YearService::getSessionYearId();
        if (!$anneeId) {
            return;
        }

        $groupe = Groupe::where('nom', $row['groupe'])
            ->where('annee_scolaire_id', $anneeId)
            ->first();

        if (!$groupe) {
            return;
        }

        $formateur = Formateur::where('user_id', $user->id)->first();
        if (!$formateur) {
            return;
        }

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
