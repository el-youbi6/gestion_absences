<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Formateur;
use App\Models\Groupe;
use App\Models\Stagiaire;
use App\Models\User;
use App\Services\AcademicYearService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserManagementController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorizeAdmin($request);

        $role = $request->query('role');
        $cin = trim((string) $request->query('cin', ''));

        $users = User::query()
            ->with(['stagiaire.groupe', 'formateur'])
            ->when(in_array($role, ['surveillant', 'stagiaire', 'formateur'], true), fn ($query) => $query->where('role', $role))
            ->when($cin !== '', fn ($query) => $query->where('cin', 'like', "%{$cin}%"))
            ->whereIn('role', ['surveillant', 'stagiaire', 'formateur'])
            ->orderBy('nom')
            ->orderBy('prenom')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (User $user) => [
                'id' => $user->id,
                'nom' => $user->nom,
                'prenom' => $user->prenom,
                'cin' => $user->cin,
                'email' => $user->email,
                'role' => $user->role,
                'groupe_id' => $user->stagiaire?->groupe_id,
                'groupe' => $user->stagiaire?->groupe?->nom,
            ]);

        $anneeId = AcademicYearService::getSessionAcademicYearId();

        return Inertia::render('admin/Users', [
            'users' => $users,
            'filters' => [
                'role' => $role,
                'cin' => $cin,
            ],
            'groupes' => Groupe::query()
                ->where('annee_scolaire_id', $anneeId)
                ->orderBy('nom')
                ->get(['id', 'nom']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $validated = $this->validatedData($request);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'nom' => $validated['nom'],
                'prenom' => $validated['prenom'],
                'cin' => $validated['cin'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => $validated['role'],
            ]);

            $this->syncProfile($user, $validated);
        });

        return back()->with('success', 'Utilisateur ajoute avec succes.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $validated = $this->validatedData($request, $user);

        DB::transaction(function () use ($user, $validated) {
            $data = [
                'nom' => $validated['nom'],
                'prenom' => $validated['prenom'],
                'cin' => $validated['cin'],
                'email' => $validated['email'],
                'role' => $validated['role'],
            ];

            if (! empty($validated['password'])) {
                $data['password'] = $validated['password'];
            }

            $user->update($data);
            $this->syncProfile($user, $validated);
        });

        return back()->with('success', 'Utilisateur modifie avec succes.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->authorizeAdmin($request);

        if ($request->user()->is($user)) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $user->delete();

        return back()->with('success', 'Utilisateur supprime avec succes.');
    }

    private function validatedData(Request $request, ?User $user = null): array
    {
        $anneeId = AcademicYearService::getSessionAcademicYearId();

        return $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'cin' => ['required', 'string', 'max:50', Rule::unique('users', 'cin')->ignore($user?->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'role' => ['required', Rule::in(['surveillant', 'stagiaire', 'formateur'])],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8'],
            'groupe_id' => [
                'nullable',
                'required_if:role,stagiaire',
                Rule::exists('groupes', 'id')->where('annee_scolaire_id', $anneeId),
            ],
        ]);
    }

    private function syncProfile(User $user, array $validated): void
    {
        if ($validated['role'] === 'formateur') {
            Formateur::firstOrCreate(['user_id' => $user->id]);
        } else {
            $user->formateur()?->delete();
        }

        if ($validated['role'] === 'stagiaire') {
            Stagiaire::updateOrCreate(
                ['user_id' => $user->id],
                ['groupe_id' => $validated['groupe_id']]
            );
        } else {
            $user->stagiaire()?->delete();
        }
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()?->role === 'admin', 403);
    }
}
