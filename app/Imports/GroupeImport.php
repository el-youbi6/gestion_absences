<?php

namespace App\Imports;

use App\Models\Groupe;
use App\Models\Filiere;
use App\Services\YearService;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class GroupeImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        if (empty($row['nom']) || empty($row['nom_filiere'])) {
            return null;
        }

        $filiere = Filiere::where('nom', $row['nom_filiere'])->first();
        if (!$filiere) {
            return null;
        }

        $anneeId = YearService::getSessionYearId();
        if (!$anneeId) {
            return null;
        }

        return Groupe::updateOrCreate(
            [
                'nom' => $row['nom'],
                'annee_scolaire_id' => $anneeId
            ],
            [
                'filiere_id' => $filiere->id,
                'annee_scolaire_id' => $anneeId
            ]
        );
    }
}