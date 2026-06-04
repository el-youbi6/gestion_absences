<?php

namespace App\Imports;

use App\Models\Formateur;
use App\Models\Groupe;
use App\Models\Module;
use App\Services\AcademicYearService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class FormateurAssignmentImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows): void
    {
        $anneeId = AcademicYearService::getSessionAcademicYearId();

        if (! $anneeId) {
            return;
        }

        foreach ($rows as $row) {
            $cin = trim((string) ($row['formateur_cin'] ?? ''));

            if ($cin === '') {
                continue;
            }

            $formateur = Formateur::whereHas('user', fn ($query) => $query
                ->where('cin', $cin)
                ->where('role', 'formateur'))
                ->first();

            if (! $formateur) {
                continue;
            }

            $groupeName = trim((string) ($row['groupe'] ?? ''));
            $moduleName = trim((string) ($row['module'] ?? ''));

            if ($groupeName !== '') {
                $groupe = Groupe::where('nom', $groupeName)
                    ->where('annee_scolaire_id', $anneeId)
                    ->first();

                if ($groupe) {
                    $this->upsertAssignment('formateur_groupe', [
                        'formateur_id' => $formateur->id,
                        'groupe_id' => $groupe->id,
                        'annee_scolaire_id' => $anneeId,
                    ]);
                }
            }

            if ($moduleName !== '') {
                $module = Module::where('nom', $moduleName)->first();

                if ($module) {
                    $this->upsertAssignment('formateur_module', [
                        'formateur_id' => $formateur->id,
                        'module_id' => $module->id,
                        'annee_scolaire_id' => $anneeId,
                    ]);
                }
            }
        }
    }

    private function upsertAssignment(string $table, array $keys): void
    {
        DB::table($table)->updateOrInsert($keys, [
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
