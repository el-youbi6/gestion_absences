<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\FormateurAssignmentImport;
use App\Models\Formateur;
use App\Models\Groupe;
use App\Models\Module;
use App\Services\AcademicYearService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class FormateurAssignmentController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorizeAdmin($request);

        $annee = AcademicYearService::getSessionAcademicYear();
        $anneeId = $annee?->id;

        $formateurs = Formateur::query()
            ->with('user')
            ->join('users', 'users.id', '=', 'formateurs.user_id')
            ->select('formateurs.*')
            ->where('users.role', 'formateur')
            ->orderBy('users.nom')
            ->orderBy('users.prenom')
            ->get()
            ->map(function (Formateur $formateur) use ($anneeId) {
                $groupes = $formateur->groupes()
                    ->where('groupes.annee_scolaire_id', $anneeId)
                    ->where('formateur_groupe.annee_scolaire_id', $anneeId)
                    ->orderBy('groupes.nom')
                    ->get(['groupes.id', 'groupes.nom']);

                $modules = $formateur->modules()
                    ->where('formateur_module.annee_scolaire_id', $anneeId)
                    ->orderBy('modules.nom')
                    ->get(['modules.id', 'modules.nom']);

                return [
                    'id' => $formateur->id,
                    'nom' => trim($formateur->user->nom . ' ' . $formateur->user->prenom),
                    'cin' => $formateur->user->cin,
                    'groupes' => $groupes,
                    'modules' => $modules,
                ];
            });

        return Inertia::render('admin/FormateurAssignments', [
            'annee' => $annee,
            'formateurs' => $formateurs,
            'groupes' => Groupe::query()
                ->where('annee_scolaire_id', $anneeId)
                ->orderBy('nom')
                ->get(['id', 'nom']),
            'modules' => Module::query()
                ->orderBy('nom')
                ->get(['id', 'nom']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'formateur_id' => ['required', 'exists:formateurs,id'],
            'groupe_ids' => ['array'],
            'groupe_ids.*' => ['exists:groupes,id'],
            'module_ids' => ['array'],
            'module_ids.*' => ['exists:modules,id'],
        ]);

        $anneeId = AcademicYearService::getSessionAcademicYearId();

        $validGroupIds = Groupe::where('annee_scolaire_id', $anneeId)
            ->whereIn('id', array_unique($validated['groupe_ids'] ?? []))
            ->pluck('id')
            ->all();

        $moduleIds = array_unique($validated['module_ids'] ?? []);

        DB::transaction(function () use ($validated, $validGroupIds, $moduleIds, $anneeId) {
            DB::table('formateur_groupe')
                ->where('formateur_id', $validated['formateur_id'])
                ->where('annee_scolaire_id', $anneeId)
                ->delete();

            DB::table('formateur_module')
                ->where('formateur_id', $validated['formateur_id'])
                ->where('annee_scolaire_id', $anneeId)
                ->delete();

            foreach ($validGroupIds as $groupeId) {
                DB::table('formateur_groupe')->insert([
                    'formateur_id' => $validated['formateur_id'],
                    'groupe_id' => $groupeId,
                    'annee_scolaire_id' => $anneeId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($moduleIds as $moduleId) {
                DB::table('formateur_module')->insert([
                    'formateur_id' => $validated['formateur_id'],
                    'module_id' => $moduleId,
                    'annee_scolaire_id' => $anneeId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return back()->with('success', 'Affectations enregistrees pour l annee en cours.');
    }

    public function import(Request $request): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $request->validate([
            'file' => ['required', 'mimes:xlsx,xls'],
        ]);

        try {
            Excel::import(new FormateurAssignmentImport(), $request->file('file'));
        } catch (Throwable $e) {
            return back()->with('error', 'Erreur lors de l import: ' . $e->getMessage());
        }

        return back()->with('success', 'Import des affectations termine.');
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless(in_array($request->user()?->role, ['admin', 'surveillant'], true), 403);
    }
}
