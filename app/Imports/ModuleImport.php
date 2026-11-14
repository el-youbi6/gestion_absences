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
     * @return \Illuminate\Database\Eloquent\Model
     *
     * @throws \Exception
     */
    public function model(array $row)
    {
        if (empty($row['nom']) || empty($row['nom_filiere'])) {
            throw new \Exception("Le nom du module et le nom de la filière sont requis");
        }

        $filiere = Filiere::where('nom', $row['nom_filiere'])->first();
        if (!$filiere) {
            throw new \Exception("La filière {$row['nom_filiere']} n'existe pas");
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
