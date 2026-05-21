<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Groupe;
use App\Models\Stagiaire;
use App\Services\AcademicYearService;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StagiaireImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Valider les données requises
        if (empty($row['nom_group']) || empty($row['email']) || empty($row['nom']) || empty($row['prenom'])) {
            return null;
        }

        // Récupérer le groupe lié à l'année scolaire actuelle
        $anneeId = AcademicYearService::getSessionAcademicYearId();
        if (!$anneeId) {
            return null;
        }

        $groupe = Groupe::where('nom', $row['nom_group'])
            ->where('annee_scolaire_id', $anneeId)
            ->first();

        if (!$groupe) {
            return null;
        }

        // Créer ou récupérer l'utilisateur avec le rôle correct
        $user = User::firstOrCreate(
            ['email' => $row['email']],
            [
                'nom' => $row['nom'],
                'prenom' => $row['prenom'],
                'cin' => $row['cin'] ?? null,
                'password' => Hash::make($row['password'] ?? 'password'),
                'role' => 'stagiaire'
            ]
        );

        // Créer ou mettre à jour le stagiaire
        return Stagiaire::updateOrCreate(
            ['user_id' => $user->id],
            [
                'groupe_id' => $groupe->id
            ]
        );
    }
}
