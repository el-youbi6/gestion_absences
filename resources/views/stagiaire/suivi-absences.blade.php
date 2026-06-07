<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suivi des absences du stagiaire</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-slate-50 text-slate-900">
    <main class="mx-auto max-w-6xl px-4 py-8">
        <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-widest text-blue-700">Espace stagiaire</p>
                <h1 class="mt-2 text-3xl font-bold">Suivi des absences du stagiaire</h1>
                <p class="mt-1 text-sm text-slate-500">
                    Consultez vos statistiques, votre etat actuel et l historique complet de vos absences.
                </p>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">
                    Deconnexion
                </button>
            </form>
        </div>

        <section class="mb-6 rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-bold">Informations du stagiaire</h2>
            <div class="mt-4 grid gap-4 md:grid-cols-4">
                <div>
                    <p class="text-sm text-slate-500">Nom complet</p>
                    <p class="font-semibold">{{ $stagiaire->user->nom }} {{ $stagiaire->user->prenom }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500">CIN</p>
                    <p class="font-semibold">{{ $stagiaire->user->cin }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500">Groupe</p>
                    <p class="font-semibold">{{ $stagiaire->groupe?->nom ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500">Etat actuel</p>
                    @if ($absenceAujourdhui)
                        <span class="inline-flex rounded-full bg-red-50 px-3 py-1 text-sm font-semibold text-red-700">Absent</span>
                    @else
                        <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-sm font-semibold text-emerald-700">Present</span>
                    @endif
                </div>
            </div>
        </section>

        <section class="mb-6 grid gap-4 md:grid-cols-3">
            <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Total des absences</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $totalAbsences }}</p>
            </article>

            <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Absences justifiees</p>
                <p class="mt-2 text-3xl font-bold text-emerald-700">{{ $absencesJustifiees }}</p>
            </article>

            <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Absences non justifiees</p>
                <p class="mt-2 text-3xl font-bold text-red-700">{{ $absencesNonJustifiees }}</p>
            </article>
        </section>

        <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="text-lg font-bold">Historique des absences</h2>
                <p class="text-sm text-slate-500">Liste complete de vos absences avec pagination.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Date</th>
                            <th class="px-5 py-3">Module</th>
                            <th class="px-5 py-3">Seance</th>
                            <th class="px-5 py-3">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($absences as $absence)
                            <tr class="hover:bg-slate-50">
                                <td class="px-5 py-3 font-medium">{{ $absence->date }}</td>
                                <td class="px-5 py-3">{{ $absence->module?->nom ?? '-' }}</td>
                                <td class="px-5 py-3">
                                    {{ substr($absence->heure_debut, 0, 5) }} - {{ substr($absence->heure_fin, 0, 5) }}
                                </td>
                                <td class="px-5 py-3">
                                    @if ($absence->is_justified)
                                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Justifiee</span>
                                    @else
                                        <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-700">Non justifiee</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-10 text-center text-slate-500">
                                    Aucune absence trouvee.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-200 px-5 py-4">
                {{ $absences->links() }}
            </div>
        </section>
    </main>
</body>
</html>
