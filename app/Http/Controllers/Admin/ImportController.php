<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\GlobalImport;
use App\Imports\GroupeImport;
use App\Imports\StagiaireImport;
use App\Models\Filiere;
use App\Models\Groupe;
use App\Services\AcademicYearService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

class ImportController extends Controller
{
    public function index(){
        return Inertia::render('admin/Importation');
    }
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
            'type' => 'required|in:global,stagiaires,groupes',
        ]);

        AcademicYearService::initializeSession();

        $anneeId = AcademicYearService::getSessionAcademicYearId();

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

        $imports = [
            'global' => [
                'handler' => new GlobalImport(),
                'message' => 'Import global reussi',
            ],
            'stagiaires' => [
                'handler' => new StagiaireImport(),
                'message' => 'Import des stagiaires reussi',
            ],
            'groupes' => [
                'handler' => new GroupeImport(),
                'message' => 'Import des groupes reussi',
            ],
        ];

        $selectedImport = $imports[$request->type];

        try {
            Excel::import($selectedImport['handler'], $request->file('file'));
        } catch (Throwable $e) {
            return back()->with('error', 'Erreur lors de l import: ' . $e->getMessage());
        }

        return back()->with('success', $selectedImport['message']);
    }

    private function validateDependencies(string $type, string $filePath, int $anneeId): ?string
    {
        if ($type === 'groupes') {
            return $this->validateGroupesImport($filePath);
        }

        if ($type === 'stagiaires') {
            return $this->validateStagiairesImport($filePath, $anneeId);
        }

        return null;
    }

    private function validateGroupesImport(string $filePath): ?string
    {
        $rows = $this->getRowsFromSheet($filePath, 'groupes');
        $filiereNames = collect($rows)->pluck('nom_filiere')->filter()->unique()->values();

        if ($filiereNames->isEmpty()) {
            return 'Import groupes impossible: la colonne nom_filiere est vide ou introuvable.';
        }

        $existingFilieres = Filiere::whereIn('nom', $filiereNames)->pluck('nom');
        $missingFilieres = $filiereNames->diff($existingFilieres)->values();

        if ($missingFilieres->isNotEmpty()) {
            return 'Import groupes impossible: filieres introuvables: ' . $missingFilieres->implode(', ') . '.';
        }

        return null;
    }

    private function validateStagiairesImport(string $filePath, int $anneeId): ?string
    {
        $rows = $this->getRowsFromSheet($filePath, 'stagiaires');
        $groupNames = collect($rows)->pluck('nom_group')->filter()->unique()->values();

        if ($groupNames->isEmpty()) {
            return 'Import stagiaires impossible: la colonne nom_group est vide ou introuvable.';
        }

        $existingGroups = Groupe::where('annee_scolaire_id', $anneeId)
            ->whereIn('nom', $groupNames)
            ->pluck('nom');

        $missingGroups = $groupNames->diff($existingGroups)->values();

        if ($missingGroups->isNotEmpty()) {
            return 'Import stagiaires impossible: groupes introuvables dans cette annee: ' . $missingGroups->implode(', ') . '.';
        }

        return null;
    }

    private function getRowsFromSheet(string $filePath, string $sheetName): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getSheetByName($sheetName) ?: $spreadsheet->getActiveSheet();
        $data = $sheet->toArray(null, true, true, true);
        $headingRow = array_shift($data) ?: [];
        $headings = [];

        foreach ($headingRow as $column => $heading) {
            $headings[$column] = $this->normalizeHeading((string) $heading);
        }

        return collect($data)
            ->map(function (array $row) use ($headings) {
                $normalizedRow = [];

                foreach ($headings as $column => $heading) {
                    if ($heading !== '') {
                        $normalizedRow[$heading] = is_string($row[$column] ?? null)
                            ? trim($row[$column])
                            : $row[$column] ?? null;
                    }
                }

                return $normalizedRow;
            })
            ->filter(fn (array $row) => collect($row)->filter()->isNotEmpty())
            ->values()
            ->all();
    }

    private function normalizeHeading(string $heading): string
    {
        $heading = trim(strtolower($heading));
        $heading = preg_replace('/^\xEF\xBB\xBF/', '', $heading);

        return preg_replace('/\s+/', '_', $heading);
    }
}
