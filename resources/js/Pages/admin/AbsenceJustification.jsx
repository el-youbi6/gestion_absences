import { Head, router, useForm, usePage } from "@inertiajs/react";
import { CheckCircle2, FileText, Filter, Search } from "lucide-react";
import { useEffect, useState } from "react";
import { ToastContainer, toast } from "react-toastify";
import "react-toastify/dist/ReactToastify.css";
import logoOFPPT from "../../assets/ofppt.png";
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

    const escapeHtml = (value) =>
        String(value ?? "-")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");

    const generateBillet = () => {
        if (!stagiaire) return;

        const now = new Date();
        const dateGeneration = now.toLocaleDateString("fr-FR", {
            year: "numeric",
            month: "long",
            day: "numeric",
        });
        const heureGeneration = now.toLocaleTimeString("fr-FR", {
            hour: "2-digit",
            minute: "2-digit",
        });
        const nomComplet = `${stagiaire.nom || ""} ${stagiaire.prenom || ""}`.trim() || "-";

        const previewWindow = window.open("", "_blank", "width=900,height=1000");

        if (!previewWindow) {
            toast.error("Impossible d'ouvrir l'aperçu. Veuillez autoriser les fenêtres pop-up.");
            return;
        }

        previewWindow.document.open();
        previewWindow.document.write(`
            <!doctype html>
            <html lang="fr">
                <head>
                    <meta charset="utf-8" />
                    <meta name="viewport" content="width=device-width, initial-scale=1" />
                    <title>Billet d'absence</title>
                    <style>
                        @page {
                            size: A4;
                            margin: 0;
                        }

                        * {
                            box-sizing: border-box;
                        }

                        body {
                            margin: 0;
                            background: #e2e8f0;
                            color: #0f172a;
                            font-family: Arial, Helvetica, sans-serif;
                        }

                        .toolbar {
                            align-items: center;
                            background: #0f172a;
                            display: flex;
                            justify-content: center;
                            padding: 14px;
                            position: sticky;
                            top: 0;
                            z-index: 2;
                        }

                        .print-button {
                            background: #2563eb;
                            border: 0;
                            border-radius: 8px;
                            color: #ffffff;
                            cursor: pointer;
                            font-size: 14px;
                            font-weight: 700;
                            padding: 10px 22px;
                        }

                        .page {
                            align-items: center;
                            background: #ffffff;
                            display: flex;
                            justify-content: center;
                            margin: 24px auto;
                            min-height: 297mm;
                            padding: 18mm;
                            width: 210mm;
                        }

                        .ticket {
                            border: 1px solid #cbd5e1;
                            min-height: 250mm;
                            padding: 18mm 16mm 14mm;
                            position: relative;
                            width: 100%;
                        }

                        .header {
                            align-items: center;
                            border-bottom: 1px solid #e2e8f0;
                            display: flex;
                            flex-direction: column;
                            gap: 14px;
                            padding-bottom: 22px;
                            text-align: center;
                        }

                        .logo {
                            height: 72px;
                            max-width: 190px;
                            object-fit: contain;
                        }

                        h1 {
                            color: #0f172a;
                            font-size: 25px;
                            letter-spacing: 0.08em;
                            margin: 0;
                        }

                        .content {
                            margin: 34px auto 0;
                            max-width: 560px;
                        }

                        .field {
                            border-bottom: 1px solid #e2e8f0;
                            display: grid;
                            gap: 18px;
                            grid-template-columns: 210px 1fr;
                            padding: 15px 0;
                        }

                        .label {
                            color: #475569;
                            font-size: 14px;
                            font-weight: 700;
                        }

                        .value {
                            color: #0f172a;
                            font-size: 16px;
                            font-weight: 600;
                        }

                        .signature {
                            margin-left: auto;
                            margin-top: 54px;
                            min-height: 90px;
                            text-align: center;
                            width: 220px;
                        }

                        .signature-title {
                            color: #0f172a;
                            font-size: 14px;
                            font-weight: 700;
                            margin-bottom: 42px;
                        }

                        .stamp {
                            border-top: 1px solid #94a3b8;
                            color: #475569;
                            font-size: 13px;
                            padding-top: 10px;
                        }

                        .note {
                            bottom: 14mm;
                            color: #64748b;
                            font-size: 11px;
                            left: 16mm;
                            position: absolute;
                            right: 16mm;
                            text-align: center;
                        }

                        @media print {
                            body {
                                background: #ffffff;
                            }

                            .toolbar {
                                display: none;
                            }

                            .page {
                                margin: 0;
                                padding: 18mm;
                            }
                        }
                    </style>
                </head>
                <body>
                    <div class="toolbar">
                        <button class="print-button" type="button" onclick="window.print()">Imprimer</button>
                    </div>
                    <main class="page">
                        <section class="ticket">
                            <header class="header">
                                <img class="logo" src="${logoOFPPT}" alt="Logo de l'établissement" />
                                <h1>BILLET D'ABSENCE</h1>
                            </header>

                            <div class="content">
                                <div class="field">
                                    <div class="label">Nom du stagiaire</div>
                                    <div class="value">${escapeHtml(nomComplet)}</div>
                                </div>
                                <div class="field">
                                    <div class="label">Filière</div>
                                    <div class="value">${escapeHtml(stagiaire.filiere)}</div>
                                </div>
                                <div class="field">
                                    <div class="label">Date de génération</div>
                                    <div class="value">${escapeHtml(dateGeneration)}</div>
                                </div>
                                <div class="field">
                                    <div class="label">Heure de génération</div>
                                    <div class="value">${escapeHtml(heureGeneration)}</div>
                                </div>

                                <div class="signature">
                                    <div class="signature-title">Signature du responsable</div>
                                    <div class="stamp">Cachet</div>
                                </div>
                            </div>

                            <p class="note">Document généré automatiquement par le système de gestion des absences.</p>
                        </section>
                    </main>
                    <script>
                        window.onafterprint = function () {
                            window.close();
                        };
                    </script>
                </body>
            </html>
        `);
        previewWindow.document.close();
        previewWindow.focus();
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
                                            <th className="px-5 py-3 text-right">Billet</th>
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
                                                <td className="px-5 py-3 text-right">
                                                    {absence.is_justified ? (
                                                        <button
                                                            type="button"
                                                            onClick={generateBillet}
                                                            className="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:border-blue-300 hover:text-blue-700"
                                                        >
                                                            <FileText className="h-4 w-4" />
                                                            Générer le billet
                                                        </button>
                                                    ) : (
                                                        <span className="text-xs text-slate-400">-</span>
                                                    )}
                                                </td>
                                            </tr>
                                        ))}

                                        {absences.length === 0 && (
                                            <tr>
                                                <td colSpan="6" className="px-5 py-10 text-center text-sm text-slate-500">
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
