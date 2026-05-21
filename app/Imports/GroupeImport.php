<?php

namespace App\Imports;

use App\Models\Groupe;
use App\Models\Filiere;
use App\Services\AcademicYearService;
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
        // Valider les données requises
        if (empty($row['nom']) || empty($row['nom_filiere'])) {
            return null;
        }

        // Récupérer la filière
        $filiere = Filiere::where('nom', $row['nom_filiere'])->first();
        if (!$filiere) {
            return null;
        }

        // Récupérer l'année scolaire depuis la session
        $anneeId = AcademicYearService::getSessionAcademicYearId();
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