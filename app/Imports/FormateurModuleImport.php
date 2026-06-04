<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Module;
use App\Models\Formateur;
use App\Services\AcademicYearService;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class FormateurModuleImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return void
     */
    public function model(array $row)
    {
        // Valider les données requises
        if (empty($row['formateur_cin']) || empty($row['module'])) {
            return;
        }

        // Récupérer le formateur par CIN
        $user = User::where('cin', $row['formateur_cin'])->first();
        if (!$user) {
            return;
        }

        // Récupérer le module
        $module = Module::where('nom', $row['module'])->first();
        if (!$module) {
            return;
        }

        // Récupérer le formateur
        $formateur = Formateur::where('user_id', $user->id)->first();
        if (!$formateur) {
            return;
        }

        $anneeId = AcademicYearService::getSessionAcademicYearId();
        if (!$anneeId) {
            return;
        }

        // Associer le formateur au module pour l'annee active.
        DB::table('formateur_module')->updateOrInsert(
            [
                'formateur_id' => $formateur->id,
                'module_id' => $module->id,
                'annee_scolaire_id' => $anneeId,
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
