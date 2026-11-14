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
     * @return \Illuminate\Database\Eloquent\Model
     *
     * @throws \Exception
     */
    public function model(array $row)
    
    {
        if (empty($row['nom']) || empty($row['nom_filiere'])) {
            throw new \Exception("Le nom du groupe et le nom de la filière sont requis");
        }

        $filiere = Filiere::where('nom', $row['nom_filiere'])->first();
        if (!$filiere) {
            throw new \Exception("La filière {$row['nom_filiere']} n'existe pas");
        }

        $anneeId = YearService::getSessionYearId();
        if (!$anneeId) {
            throw new \Exception("Impossible de récupérer l'année scolaire en session");
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