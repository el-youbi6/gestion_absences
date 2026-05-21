<?php

namespace App\Imports;


use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class GlobalImport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'filieres' => new FiliereImport(),
            'groupes' => new GroupeImport(),
            'modules' => new ModuleImport(),
            'formateurs' => new FormateurImport(),
            'stagiaires' => new StagiaireImport(),
            'formateur_groupe' => new FormateurGroupeImport(),
            'formateur_module' => new FormateurModuleImport(),
        ];
    }
}
