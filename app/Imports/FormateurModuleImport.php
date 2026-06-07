<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Module;
use App\Models\Formateur;
use App\Services\YearService;
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
        if (empty($row['formateur_cin']) || empty($row['module'])) {
            return;
        }

        $user = User::where('cin', $row['formateur_cin'])->first();
        if (!$user) {
            return;
        }

        $module = Module::where('nom', $row['module'])->first();
        if (!$module) {
            return;
        }

        $formateur = Formateur::where('user_id', $user->id)->first();
        if (!$formateur) {
            return;
        }

        $anneeId = YearService::getSessionYearId();
        if (!$anneeId) {
            return;
        }

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
