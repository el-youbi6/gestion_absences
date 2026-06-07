import { Head, router, useForm, usePage } from "@inertiajs/react";
import { CheckCircle2, Filter, Search } from "lucide-react";
import { useEffect, useState } from "react";
import { ToastContainer, toast } from "react-toastify";
import "react-toastify/dist/ReactToastify.css";
import MainLayout from "../../Layouts/MainLayout";

export default function AbsenceJustification({ filters, stagiaire, absences }) {
    const { flash } = usePage().props;
    const [selectedAbsences, setSelectedAbsences] = useState([]);

    const searchForm = useForm({
        cin: filters.cin || "",
        date_debut: filters.date_debut || "",
        date_fin: filters.date_fin || "",
    });

    const justifyForm = useForm({
        stagiaire_id: stagiaire?.id || "",
        absence_ids: [],
        cin: filters.cin || "",
        date_debut: filters.date_debut || "",
        date_fin: filters.date_fin || "",
    });

    useEffect(() => {
        if (flash.success) toast.success(flash.success);
        if (flash.error) toast.error(flash.error);
    }, [flash?.success, flash?.error]);

    useEffect(() => {
        setSelectedAbsences([]);
        justifyForm.setData({
            stagiaire_id: stagiaire?.id || "",
            absence_ids: [],
            cin: filters.cin || "",
            date_debut: filters.date_debut || "",
            date_fin: filters.date_fin || "",
        });
    }, [stagiaire?.id, filters.cin, filters.date_debut, filters.date_fin]);

    const submitSearch = (event) => {
        event.preventDefault();

        router.get("/admin/justification-absences", searchForm.data, {
            preserveScroll: true,
        });
    };

    const toggleAbsence = (absenceId) => {
        const nextSelected = selectedAbsences.includes(absenceId)
            ? selectedAbsences.filter((id) => id !== absenceId)
            : [...selectedAbsences, absenceId];

        setSelectedAbsences(nextSelected);
        justifyForm.setData("absence_ids", nextSelected);
    };

    const submitJustification = (event) => {
        event.preventDefault();

        justifyForm.post("/admin/justification-absences", {
            preserveScroll: true,
        });
    };

    return (
        <div className="space-y-6">
            <Head title="Justification des absences" />
            <ToastContainer position="top-right" autoClose={3500} />

            <div>
                <p className="text-sm font-semibold uppercase tracking-[0.18em] text-blue-700">Administration</p>
                <h1 className="mt-2 text-3xl font-bold text-slate-900">Justification des absences</h1>
                <p className="mt-1 text-sm text-slate-500">
                    Rechercher un stagiaire par CIN, filtrer ses absences puis justifier les lignes selectionnees.
                </p>
            </div>

            <section className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <form onSubmit={submitSearch} className="grid gap-4 lg:grid-cols-[1fr_200px_200px_auto] lg:items-end">
                    <div>
                        <label className="mb-1 block text-sm font-medium text-slate-700">CIN du stagiaire</label>
                        <input
                            type="text"
                            value={searchForm.data.cin}
                            onChange={(event) => searchForm.setData("cin", event.target.value)}
                            className="w-full rounded-lg border-slate-300 text-sm"
                            placeholder="Exemple: AB123456"
                        />
                        {searchForm.errors.cin && <p className="mt-1 text-sm text-red-600">{searchForm.errors.cin}</p>}
                    </div>

                    <div>
                        <label className="mb-1 block text-sm font-medium text-slate-700">Date debut</label>
                        <input
                            type="date"
                            value={searchForm.data.date_debut}
                            onChange={(event) => searchForm.setData("date_debut", event.target.value)}
                            className="w-full rounded-lg border-slate-300 text-sm"
                        />
                        {searchForm.errors.date_debut && <p className="mt-1 text-sm text-red-600">{searchForm.errors.date_debut}</p>}
                    </div>

                    <div>
                        <label className="mb-1 block text-sm font-medium text-slate-700">Date fin</label>
                        <input
                            type="date"
                            value={searchForm.data.date_fin}
                            onChange={(event) => searchForm.setData("date_fin", event.target.value)}
                            className="w-full rounded-lg border-slate-300 text-sm"
                        />
                        {searchForm.errors.date_fin && <p className="mt-1 text-sm text-red-600">{searchForm.errors.date_fin}</p>}
                    </div>

                    <button
                        disabled={searchForm.processing}
                        className="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-semibold text-white disabled:bg-slate-400"
                    >
                        <Search className="h-4 w-4" />
                        Rechercher
                    </button>
                </form>
            </section>

            {filters.cin && !stagiaire && (
                <section className="rounded-lg border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
                    Aucun stagiaire trouve avec ce CIN.
                </section>
            )}

            {stagiaire && (
                <>
                    <section className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <InfoCard label="Nom complet" value={`${stagiaire.nom || ""} ${stagiaire.prenom || ""}`} />
                        <InfoCard label="CIN" value={stagiaire.cin || "-"} />
                        <InfoCard label="Groupe" value={stagiaire.groupe || "-"} />
                        <InfoCard label="Filiere" value={stagiaire.filiere || "-"} />
                    </section>

                    <section className="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                        <div className="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 md:flex-row md:items-center md:justify-between">
                            <div>
                                <h2 className="text-lg font-bold text-slate-900">Absences du stagiaire</h2>
                                <p className="text-sm text-slate-500">
                                    {absences.length} absence(s) affichee(s) selon les filtres.
                                </p>
                            </div>
                            <div className="inline-flex items-center gap-2 rounded-lg bg-slate-100 px-3 py-2 text-sm text-slate-700">
                                <Filter className="h-4 w-4" />
                                Periode selectionnee
                            </div>
                        </div>

                        <form onSubmit={submitJustification}>
                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-slate-200 text-sm">
                                    <thead className="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
                                        <tr>
                                            <th className="w-12 px-5 py-3">Choix</th>
                                            <th className="px-5 py-3">Date</th>
                                            <th className="px-5 py-3">Horaire</th>
                                            <th className="px-5 py-3">Module</th>
                                            <th className="px-5 py-3">Statut</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-slate-100 bg-white">
                                        {absences.map((absence) => (
                                            <tr key={absence.id} className="hover:bg-slate-50">
                                                <td className="px-5 py-3">
                                                    <input
                                                        type="checkbox"
                                                        disabled={absence.is_justified}
                                                        checked={selectedAbsences.includes(absence.id)}
                                                        onChange={() => toggleAbsence(absence.id)}
                                                        className="rounded border-slate-300 text-blue-700 focus:ring-blue-700 disabled:bg-slate-100"
                                                    />
                                                </td>
                                                <td className="px-5 py-3 font-medium text-slate-900">{absence.date}</td>
                                                <td className="px-5 py-3 text-slate-700">
                                                    {absence.heure_debut} - {absence.heure_fin}
                                                </td>
                                                <td className="px-5 py-3 text-slate-700">{absence.module || "-"}</td>
                                                <td className="px-5 py-3">
                                                    {absence.is_justified ? (
                                                        <span className="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                                            Justifiee
                                                        </span>
                                                    ) : (
                                                        <span className="rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-700">
                                                            Non justifiee
                                                        </span>
                                                    )}
                                                </td>
                                            </tr>
                                        ))}

                                        {absences.length === 0 && (
                                            <tr>
                                                <td colSpan="5" className="px-5 py-10 text-center text-sm text-slate-500">
                                                    Aucune absence trouvee pour cette recherche.
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>

                            {justifyForm.errors.absence_ids && (
                                <p className="border-t border-slate-100 px-5 pt-4 text-sm text-red-600">{justifyForm.errors.absence_ids}</p>
                            )}

                            <div className="flex flex-col gap-3 border-t border-slate-200 px-5 py-4 md:flex-row md:items-center md:justify-between">
                                <p className="text-sm text-slate-500">{selectedAbsences.length} absence(s) selectionnee(s).</p>
                                <button
                                    disabled={justifyForm.processing || selectedAbsences.length === 0}
                                    className="inline-flex items-center justify-center gap-2 rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white disabled:bg-slate-400"
                                >
                                    <CheckCircle2 className="h-4 w-4" />
                                    Justifier les absences
                                </button>
                            </div>
                        </form>
                    </section>
                </>
            )}
        </div>
    );
}

function InfoCard({ label, value }) {
    return (
        <article className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <p className="text-sm font-medium text-slate-500">{label}</p>
            <p className="mt-2 truncate text-lg font-bold text-slate-900">{value}</p>
        </article>
    );
}

AbsenceJustification.layout = (page) => <MainLayout>{page}</MainLayout>;
