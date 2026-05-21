<?php

namespace App\Imports;

use App\Models\Filiere;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class FiliereImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Valider qu'il y a bien un nom
        if (empty($row['nom'])) {
            return null;
        }

        return Filiere::firstOrCreate(
            ['nom' => $row['nom']]
        );
    }
}