import { Head, router } from '@inertiajs/react';
import { AlertCircle, BarChart3, CalendarDays, CheckCircle2, FileText } from 'lucide-react';
import MainLayout from '../../Layouts/MainLayout';
import YearSelect from '../../Components/YearSelect';

export default function RapportModule({ annee, modules, selectedModuleId, selectedModule, summary, stagiaires, recentAbsences }) {
    const changeModule = (moduleId) => {
        router.get('/absences/rapport-modules', { module_id: moduleId }, {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        });
    };

    return (
        <div className="space-y-6">
            <Head title="Rapport absences par module" />

            <div className="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <p className="text-sm font-semibold uppercase tracking-[0.18em] text-emerald-700">Formateur</p>
                    <h1 className="mt-2 text-3xl font-bold text-slate-900">Rapport absences par module</h1>
                    <p className="mt-1 text-sm text-slate-500">
                        Consultation directe pour l annee scolaire {annee?.libelle || 'active'}.
                    </p>
                </div>
                <YearSelect />
            </div>

            <section className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <div className="grid gap-4 lg:grid-cols-[1fr_320px] lg:items-end">
                    <div>
                        <h2 className="text-lg font-bold text-slate-900">{selectedModule?.nom || 'Aucun module selectionne'}</h2>
                        <p className="mt-1 text-sm text-slate-500">{selectedModule?.filiere || 'Choisissez un module pour generer le rapport.'}</p>
                    </div>
                    <label>
                        <span className="mb-2 block text-sm font-semibold text-slate-700">Module</span>
                        <select
                            value={selectedModuleId || ''}
                            onChange={(event) => changeModule(event.target.value)}
                            className="w-full rounded-lg border-slate-300 text-sm"
                        >
                            {modules.map((module) => (
                                <option key={module.id} value={module.id}>
                                    {module.nom}
                                </option>
                            ))}
                        </select>
                    </label>
                </div>
            </section>

            <section className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <StatCard label="Total module" value={summary.total_absences} icon={<BarChart3 className="h-5 w-5" />} tone="emerald" />
                <StatCard label="Aujourd hui" value={summary.today_absences} icon={<CalendarDays className="h-5 w-5" />} tone="blue" />
                <StatCard label="Non justifiees" value={summary.unjustified_absences} icon={<AlertCircle className="h-5 w-5" />} tone="red" />
                <StatCard label="Justifiees" value={summary.justified_absences} icon={<CheckCircle2 className="h-5 w-5" />} tone="slate" />
            </section>

            <div className="grid gap-6 xl:grid-cols-[1fr_420px]">
                <section className="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 px-5 py-4">
                        <h2 className="text-lg font-bold text-slate-900">Stagiaires concernes</h2>
                        <p className="text-sm text-slate-500">Classement pour le module selectionne.</p>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-slate-200">
                            <thead className="bg-slate-50">
                                <tr>
                                    <Th>Stagiaire</Th>
                                    <Th>Groupe</Th>
                                    <Th>Total</Th>
                                    <Th>Non justifiees</Th>
                                    <Th>Justifiees</Th>
                                    <Th>Derniere absence</Th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {stagiaires.map((stagiaire) => (
                                    <tr key={stagiaire.id} className="hover:bg-slate-50">
                                        <Td>
                                            <p className="font-semibold text-slate-900">{stagiaire.nom}</p>
                                            <p className="text-xs text-slate-500">{stagiaire.cin || '-'}</p>
                                        </Td>
                                        <Td>{stagiaire.groupe || '-'}</Td>
                                        <Td>{stagiaire.total_absences}</Td>
                                        <Td>{stagiaire.unjustified_absences}</Td>
                                        <Td>{stagiaire.justified_absences}</Td>
                                        <Td>{stagiaire.last_absence_date || '-'}</Td>
                                    </tr>
                                ))}

                                {stagiaires.length === 0 && (
                                    <tr>
                                        <td colSpan="6" className="px-5 py-10 text-center text-sm text-slate-500">
                                            Aucune absence pour ce module.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </section>

                <section className="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 px-5 py-4">
                        <h2 className="flex items-center gap-2 text-lg font-bold text-slate-900">
                            <FileText className="h-5 w-5 text-emerald-700" />
                            Dernieres absences
                        </h2>
                    </div>
                    <div className="divide-y divide-slate-100">
                        {recentAbsences.map((absence) => (
                            <div key={absence.id} className="px-5 py-4">
                                <div className="flex items-start justify-between gap-3">
                                    <div className="min-w-0">
                                        <p className="truncate font-semibold text-slate-900">{absence.stagiaire || '-'}</p>
                                        <p className="text-xs text-slate-500">
                                            {absence.groupe || '-'} - {absence.date} - {absence.heure_debut}/{absence.heure_fin}
                                        </p>
                                    </div>
                                    <span className={`shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold ${
                                        absence.is_justified
                                            ? 'bg-emerald-50 text-emerald-700'
                                            : 'bg-red-50 text-red-700'
                                    }`}>
                                        {absence.is_justified ? 'Justifiee' : 'Non justifiee'}
                                    </span>
                                </div>
                            </div>
                        ))}

                        {recentAbsences.length === 0 && (
                            <div className="px-5 py-10 text-center text-sm text-slate-500">Aucune absence recente.</div>
                        )}
                    </div>
                </section>
            </div>
        </div>
    );
}

function StatCard({ label, value, icon, tone }) {
    const tones = {
        emerald: 'bg-emerald-50 text-emerald-700',
        blue: 'bg-blue-50 text-blue-700',
        red: 'bg-red-50 text-red-700',
        slate: 'bg-slate-100 text-slate-700',
    };

    return (
        <article className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <div className={`mb-4 inline-flex rounded-lg p-2 ${tones[tone]}`}>{icon}</div>
            <p className="text-sm font-medium text-slate-500">{label}</p>
            <p className="mt-2 text-3xl font-bold text-slate-900">{value}</p>
        </article>
    );
}

function Th({ children }) {
    return <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">{children}</th>;
}

function Td({ children }) {
    return <td className="whitespace-nowrap px-4 py-3 text-sm text-slate-700">{children}</td>;
}

RapportModule.layout = (page) => <MainLayout>{page}</MainLayout>;
