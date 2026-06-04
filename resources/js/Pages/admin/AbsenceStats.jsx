import { Head } from '@inertiajs/react';
import { AlertCircle, BarChart3, CalendarDays, CheckCircle2, TrendingUp } from 'lucide-react';
import MainLayout from '../../Layouts/MainLayout';
import AcademicYearSelector from '../../Components/AcademicYearSelector';

export default function AbsenceStats({ annee, summary, statsByFiliere, topStagiaires }) {
    const maxFiliereAbsences = Math.max(...statsByFiliere.map((item) => item.total_absences), 1);

    return (
        <div className="space-y-6">
            <Head title="Statistiques absences" />

            <div className="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <p className="text-sm font-semibold uppercase tracking-[0.18em] text-blue-700">Administration</p>
                    <h1 className="mt-2 text-3xl font-bold text-slate-900">Statistiques globales des absences</h1>
                    <p className="mt-1 text-sm text-slate-500">
                        Vue d ensemble pour l annee scolaire {annee?.libelle || 'active'}.
                    </p>
                </div>
                <AcademicYearSelector />
            </div>

            <section className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <StatCard label="Total absences" value={summary.total_absences} icon={<BarChart3 className="h-5 w-5" />} tone="blue" />
                <StatCard label="Absences aujourd hui" value={summary.today_absences} icon={<CalendarDays className="h-5 w-5" />} tone="emerald" />
                <StatCard label="Non justifiees" value={summary.unjustified_absences} icon={<AlertCircle className="h-5 w-5" />} tone="red" />
                <StatCard label="Justifiees" value={summary.justified_absences} icon={<CheckCircle2 className="h-5 w-5" />} tone="slate" />
            </section>

            <div className="grid gap-6 xl:grid-cols-[1fr_440px]">
                <section className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <div className="mb-5 flex items-center justify-between">
                        <div>
                            <h2 className="text-lg font-bold text-slate-900">Statistique par filiere</h2>
                            <p className="text-sm text-slate-500">Volume d absences par filiere avec statut.</p>
                        </div>
                        <TrendingUp className="h-5 w-5 text-blue-700" />
                    </div>

                    <div className="space-y-4">
                        {statsByFiliere.map((filiere) => (
                            <div key={filiere.id} className="rounded-lg border border-slate-100 bg-slate-50 p-4">
                                <div className="flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <p className="font-semibold text-slate-900">{filiere.nom}</p>
                                        <p className="text-xs text-slate-500">
                                            {filiere.justified_absences} justifiees, {filiere.unjustified_absences} non justifiees
                                        </p>
                                    </div>
                                    <span className="rounded-full bg-white px-3 py-1 text-sm font-bold text-slate-900 shadow-sm">
                                        {filiere.total_absences}
                                    </span>
                                </div>
                                <div className="mt-3 h-2 overflow-hidden rounded-full bg-white">
                                    <div
                                        className="h-full rounded-full bg-blue-700"
                                        style={{ width: `${(filiere.total_absences / maxFiliereAbsences) * 100}%` }}
                                    />
                                </div>
                            </div>
                        ))}

                        {statsByFiliere.length === 0 && (
                            <div className="rounded-lg border border-dashed border-slate-300 py-10 text-center text-sm text-slate-500">
                                Aucune absence pour cette annee.
                            </div>
                        )}
                    </div>
                </section>

                <section className="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                    <div className="border-b border-slate-200 px-5 py-4">
                        <h2 className="text-lg font-bold text-slate-900">Top 10 stagiaires</h2>
                        <p className="text-sm text-slate-500">Les stagiaires avec le plus d absences.</p>
                    </div>
                    <div className="divide-y divide-slate-100">
                        {topStagiaires.map((stagiaire, index) => (
                            <div key={stagiaire.id} className="flex items-center gap-4 px-5 py-4">
                                <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-900 text-sm font-bold text-white">
                                    {index + 1}
                                </div>
                                <div className="min-w-0 flex-1">
                                    <p className="truncate font-semibold text-slate-900">{stagiaire.nom}</p>
                                    <p className="truncate text-xs text-slate-500">
                                        {stagiaire.cin || '-'} - {stagiaire.groupe || '-'} - {stagiaire.filiere || '-'}
                                    </p>
                                </div>
                                <div className="text-right">
                                    <p className="text-lg font-bold text-blue-700">{stagiaire.total_absences}</p>
                                    <p className="text-xs text-slate-500">{stagiaire.last_absence_date || '-'}</p>
                                </div>
                            </div>
                        ))}

                        {topStagiaires.length === 0 && (
                            <div className="px-5 py-10 text-center text-sm text-slate-500">Aucune donnee disponible.</div>
                        )}
                    </div>
                </section>
            </div>
        </div>
    );
}

function StatCard({ label, value, icon, tone }) {
    const tones = {
        blue: 'bg-blue-50 text-blue-700',
        emerald: 'bg-emerald-50 text-emerald-700',
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

AbsenceStats.layout = (page) => <MainLayout>{page}</MainLayout>;
