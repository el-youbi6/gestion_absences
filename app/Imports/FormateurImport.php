<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Formateur;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class FormateurImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        if (empty($row['email']) || empty($row['nom']) || empty($row['prenom'])) {
            return null;
        }

        $user = User::firstOrCreate(
            ['email' => $row['email']],
            [
                'nom' => $row['nom'],
                'prenom' => $row['prenom'],
                'cin' => $row['cin'] ?? null,
                'password' => Hash::make($row['password'] ?? 'password'),
                'role' => 'formateur'
            ]
        );

        return Formateur::updateOrCreate(
            ['user_id' => $user->id],
            ['user_id' => $user->id]
        );
    }
}