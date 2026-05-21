<?php

namespace App\Imports;

use App\Models\Module;
use App\Models\Filiere;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ModuleImport implements ToModel, WithHeadingRow
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

        $filiere = Filiere::where('nom', $row['nom_filiere'])->first();
        if (!$filiere) {
            return null;
        }

        return Module::updateOrCreate(
            [
                'nom' => $row['nom'],
                'filiere_id' => $filiere->id
            ],
            [
                'filiere_id' => $filiere->id
            ]
        );
    }
}
