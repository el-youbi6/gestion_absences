<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\GlobalImport;
use App\Imports\GroupeImport;
use App\Imports\StagiaireImport;
use App\Models\Filiere;
use App\Models\Groupe;
use App\Services\YearService;
use Exception;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
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

        $dependencyError = $this->validateDependencies(
            $request->type,
            $request->file('file')->getRealPath(),
            $anneeId
        );

        if ($dependencyError) {
            return back()->with('error', $dependencyError);
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

    public function validateDependencies($type, $filePath, $anneeId)
    {
        if ($type === 'groupes') {
            return $this->validateGroupesImport($filePath);
        }

        if ($type === 'stagiaires') {
            return $this->validateStagiairesImport($filePath, $anneeId);
        }

        return null;
    }

    public function validateGroupesImport($filePath)
    {
        $rows = $this->getRowsFromSheet($filePath, 'groupes');
        $filiereNames = [];

        foreach ($rows as $row) {
            if (! empty($row['nom_filiere']) && ! in_array($row['nom_filiere'], $filiereNames, true)) {
                $filiereNames[] = $row['nom_filiere'];
            }
        }

        if (count($filiereNames) === 0) {
            return 'Import groupes impossible: la colonne nom_filiere est vide ou introuvable.';
        }

        $existingFilieres = Filiere::whereIn('nom', $filiereNames)
            ->pluck('nom')
            ->all();

        $missingFilieres = [];

        foreach ($filiereNames as $filiereName) {
            if (! in_array($filiereName, $existingFilieres, true)) {
                $missingFilieres[] = $filiereName;
            }
        }

        if (count($missingFilieres) > 0) {
            return 'Import groupes impossible: filieres introuvables: ' . implode(', ', $missingFilieres) . '.';
        }

        return null;
    }

    public function validateStagiairesImport($filePath, $anneeId)
    {
        $rows = $this->getRowsFromSheet($filePath, 'stagiaires');
        $groupNames = [];

        foreach ($rows as $row) {
            if (! empty($row['nom_group']) && ! in_array($row['nom_group'], $groupNames, true)) {
                $groupNames[] = $row['nom_group'];
            }
        }

        if (count($groupNames) === 0) {
            return 'Import stagiaires impossible: la colonne nom_group est vide ou introuvable.';
        }

        $existingGroups = Groupe::where('annee_scolaire_id', $anneeId)
            ->whereIn('nom', $groupNames)
            ->pluck('nom')
            ->all();

        $missingGroups = [];

        foreach ($groupNames as $groupName) {
            if (! in_array($groupName, $existingGroups, true)) {
                $missingGroups[] = $groupName;
            }
        }

        if (count($missingGroups) > 0) {
            return 'Import stagiaires impossible: groupes introuvables dans cette annee: ' . implode(', ', $missingGroups) . '.';
        }

        return null;
    }

    public function getRowsFromSheet($filePath, $sheetName)
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getSheetByName($sheetName) ?: $spreadsheet->getActiveSheet();
        $data = $sheet->toArray(null, true, true, true);
        $headingRow = array_shift($data) ?: [];
        $headings = [];

        foreach ($headingRow as $column => $heading) {
            $headings[$column] = $this->normalizeHeading((string) $heading);
        }

        $rows = [];

        foreach ($data as $row) {
            $normalizedRow = [];
            $hasValue = false;

            foreach ($headings as $column => $heading) {
                if ($heading === '') {
                    continue;
                }

                $value = $row[$column] ?? null;

                if (is_string($value)) {
                    $value = trim($value);
                }

                if ($value !== null && $value !== '') {
                    $hasValue = true;
                }

                $normalizedRow[$heading] = $value;
            }

            if ($hasValue) {
                $rows[] = $normalizedRow;
            }
        }

        return $rows;
    }

    public function normalizeHeading($heading)
    {
        $heading = trim(strtolower($heading));
        $heading = preg_replace('/^\xEF\xBB\xBF/', '', $heading);

        return preg_replace('/\s+/', '_', $heading);
    }
}
