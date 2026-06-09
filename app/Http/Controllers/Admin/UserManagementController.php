<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\Formateur;
use App\Models\Groupe;
use App\Models\Stagiaire;
use App\Models\User;
use App\Services\YearService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserManagementController extends Controller
{


    public function index(Request $request)
    {
        $role = $request->query('role');
        $cin = $request->query('cin', '');
        $groupes = Groupe::query()
            ->where('annee_scolaire_id', YearService::getSessionYearId())
            ->orderBy('nom')
            ->get(['id', 'nom']);

        return Inertia::render('admin/Users', [
            'users' => $this->getUsers($role, $cin),
            'filters' => [
                'role' => $role,
                'cin' => $cin,
            ],
            'groupes' => $groupes,
        ]);
    }

    public function store(UserRequest $request)
    {
        $validated = $request->validated();

            $user = User::create([
                'nom' => $validated['nom'],
                'prenom' => $validated['prenom'],
                'cin' => $validated['cin'],
                'email' => $validated['email'],
                'role' => $validated['role'],
                'password' => $validated['password'],
            ]);

            if ($validated['role'] === 'formateur') {
                Formateur::firstOrCreate(['user_id' => $user->id]);
            }

            if ($validated['role'] === 'stagiaire') {
                Stagiaire::updateOrCreate(
                    ['user_id' => $user->id],
                    ['groupe_id' => $validated['groupe_id']]
                );
            }

        return back()->with('success', 'Utilisateur ajoute avec succes.');
    }

    public function update(UserRequest $request, User $user)
    {
        $validated = $request->validated();

            $user->update([
                'nom' => $validated['nom'],
                'prenom' => $validated['prenom'],
                'cin' => $validated['cin'],
                'email' => $validated['email'],
                'role' => $validated['role'],
                'password' => $validated['password'],
            ]);
            if ($validated['role'] === 'formateur') {
                Formateur::firstOrCreate(['user_id' => $user->id]);
            }
            if ($validated['role'] === 'stagiaire') {
                Stagiaire::updateOrCreate(
                    ['user_id' => $user->id],
                    ['groupe_id' => $validated['groupe_id']]
                );
            }

        return back()->with('success', 'Utilisateur modifie avec succes.');
    }

    public function destroy(Request $request, User $user)
    {
        $user->delete();

        return back()->with('success', 'Utilisateur supprime avec succes.');
    }

    public function getUsers($role, $cin)
    {
        return User::query()
            ->with(['stagiaire.groupe'])
            ->whereIn('role', ['surveillant', 'stagiaire', 'formateur'])
            ->when(in_array($role, ['surveillant', 'stagiaire', 'formateur'], true), fn($query) => $query->where('role', $role))
            ->when($cin !== '', fn($query) => $query->where('cin', 'like', "%{$cin}%"))
            ->orderBy('nom')
            ->orderBy('prenom')
            ->paginate(10)
            ->withQueryString()
            ->through(fn(User $user) => [
                'id' => $user->id,
                'nom' => $user->nom,
                'prenom' => $user->prenom,
                'cin' => $user->cin,
                'email' => $user->email,
                'role' => $user->role,
                'groupe_id' => $user->stagiaire?->groupe_id,
                'groupe' => $user->stagiaire?->groupe?->nom,
            ]);
    }

}
