<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\GlobalImport;
use App\Imports\GroupeImport;
use App\Imports\StagiaireImport;
use App\Services\YearService;
use Exception;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
// use Throwable;

class ImportController extends Controller
{
    public function index()
    {
        return Inertia::render('admin/Importation');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
            'type' => 'required|in:global,stagiaires,groupes',
        ]);

        YearService::initializeSession();

        $anneeId = YearService::getSessionYearId();

        if (!$anneeId) {
            return back()->with('error', 'Aucune annee scolaire active trouvee.');
        }

        if ($request->type === 'global') {
            $typeImport = new GlobalImport();
            $successMessage = 'Import global reussi';
        } elseif ($request->type === 'stagiaires') {
            $typeImport = new StagiaireImport();
            $successMessage = 'Import des stagiaires reussi';
        } else {
            $typeImport = new GroupeImport();
            $successMessage = 'Import des groupes reussi';
        }

        try {
            Excel::import($typeImport, $request->file('file'));
        } catch (Exception $e) {
            return back()->with('error', 'Erreur lors de l import: ' . $e->getMessage());
        }

        return back()->with('success', $successMessage);
    }

    
}
