import React, { useEffect, useMemo, useState } from "react";
import { Head, router, useForm, usePage } from "@inertiajs/react";
import { FileSpreadsheet, Save, UploadCloud } from "lucide-react";
import { ToastContainer, toast } from "react-toastify";
import "react-toastify/dist/ReactToastify.css";
import MainLayout from "../../Layouts/MainLayout";

export default function FormateurAssignments({ annee, formateurs, groupes, modules }) {
    const { flash } = usePage().props;
    const [selectedId, setSelectedId] = useState(formateurs[0]?.id || "");
    const [fileName, setFileName] = useState("");

    const selectedFormateur = useMemo(
        () => formateurs.find((formateur) => String(formateur.id) === String(selectedId)),
        [formateurs, selectedId],
    );

    const manualForm = useForm({
        formateur_id: selectedId,
        groupe_ids: [],
        module_ids: [],
    });

    const importForm = useForm({ file: null });

    useEffect(() => {
        if (flash.success) toast.success(flash.success);
        if (flash.error) toast.error(flash.error);
    }, [flash?.success, flash?.error]);

    useEffect(() => {
        manualForm.setData({
            formateur_id: selectedId,
            groupe_ids: selectedFormateur?.groupes?.map((groupe) => groupe.id) || [],
            module_ids: selectedFormateur?.modules?.map((module) => module.id) || [],
        });
    }, [selectedId, selectedFormateur]);

    const toggleValue = (field, value) => {
        const current = manualForm.data[field];
        manualForm.setData(
            field,
            current.includes(value)
                ? current.filter((item) => item !== value)
                : [...current, value],
        );
    };

    const submitManual = (event) => {
        event.preventDefault();
        manualForm.post("/admin/affectations-formateurs", { preserveScroll: true });
    };

    const submitImport = (event) => {
        event.preventDefault();
        importForm.post("/admin/affectations-formateurs/import", {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                importForm.reset();
                setFileName("");
            },
        });
    };

    return (
        <div className="space-y-6">
            <Head title="Affectations formateurs" />
            <ToastContainer position="top-right" autoClose={3500} />

            <div>
                <h1 className="text-3xl font-bold text-slate-900">Affectation des formateurs</h1>
                <p className="mt-1 text-sm text-slate-500">
                    Groupes et modules enregistres pour l annee {annee?.libelle || "active"}.
                </p>
            </div>

            <div className="grid gap-6 lg:grid-cols-[1fr_360px]">
                <section className="rounded-lg border border-slate-200 bg-white p-5">
                    <form onSubmit={submitManual} className="space-y-5">
                        <div>
                            <label className="mb-1 block text-sm font-medium text-slate-700">Formateur</label>
                            <select
                                value={selectedId}
                                onChange={(event) => setSelectedId(event.target.value)}
                                className="w-full rounded-lg border-slate-300 text-sm"
                            >
                                {formateurs.map((formateur) => (
                                    <option key={formateur.id} value={formateur.id}>
                                        {formateur.nom} - {formateur.cin}
                                    </option>
                                ))}
                            </select>
                            {manualForm.errors.formateur_id && <p className="mt-1 text-sm text-red-600">{manualForm.errors.formateur_id}</p>}
                        </div>

                        <div className="grid gap-5 xl:grid-cols-2">
                            <Checklist
                                title="Groupes de l annee en cours"
                                items={groupes}
                                selected={manualForm.data.groupe_ids}
                                onToggle={(id) => toggleValue("groupe_ids", id)}
                                emptyText="Aucun groupe pour cette annee."
                            />

                            <Checklist
                                title="Modules"
                                items={modules}
                                selected={manualForm.data.module_ids}
                                onToggle={(id) => toggleValue("module_ids", id)}
                                emptyText="Aucun module disponible."
                            />
                        </div>

                        <button
                            disabled={manualForm.processing || !selectedId}
                            className="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-700 px-5 py-2.5 text-sm font-semibold text-white disabled:bg-slate-400"
                        >
                            <Save className="h-4 w-4" />
                            Enregistrer les affectations
                        </button>
                    </form>
                </section>

                <aside className="space-y-6">
                    <section className="rounded-lg border border-slate-200 bg-white p-5">
                        <h2 className="text-lg font-semibold text-slate-900">Import Excel</h2>
                        <p className="mt-1 text-sm text-slate-500">Colonnes attendues : formateur_cin, groupe, module.</p>

                        <form onSubmit={submitImport} className="mt-5 space-y-4">
                            <label className="flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-slate-300 px-4 py-8 text-center hover:bg-slate-50">
                                <UploadCloud className="h-8 w-8 text-blue-700" />
                                <span className="mt-2 text-sm font-medium text-slate-700">Choisir un fichier .xlsx</span>
                                <input
                                    type="file"
                                    accept=".xlsx,.xls"
                                    className="hidden"
                                    onChange={(event) => {
                                        const file = event.target.files[0];
                                        importForm.setData("file", file);
                                        setFileName(file?.name || "");
                                    }}
                                />
                            </label>

                            {fileName && (
                                <div className="flex items-center gap-2 rounded-lg bg-green-50 px-3 py-2 text-sm text-green-800">
                                    <FileSpreadsheet className="h-4 w-4" />
                                    <span className="truncate">{fileName}</span>
                                </div>
                            )}

                            {importForm.errors.file && <p className="text-sm text-red-600">{importForm.errors.file}</p>}

                            <button
                                disabled={importForm.processing || !importForm.data.file}
                                className="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white disabled:bg-slate-400"
                            >
                                <UploadCloud className="h-4 w-4" />
                                Importer
                            </button>
                        </form>
                    </section>

                    <section className="rounded-lg border border-slate-200 bg-white p-5">
                        <h2 className="text-lg font-semibold text-slate-900">Apercu</h2>
                        <div className="mt-4 space-y-4">
                            {formateurs.map((formateur) => (
                                <div key={formateur.id} className="border-b border-slate-100 pb-4 last:border-b-0 last:pb-0">
                                    <p className="font-medium text-slate-900">{formateur.nom}</p>
                                    <p className="text-xs text-slate-500">{formateur.cin}</p>
                                    <p className="mt-2 text-sm text-slate-600">
                                        {formateur.groupes.length} groupe(s), {formateur.modules.length} module(s)
                                    </p>
                                </div>
                            ))}
                        </div>
                    </section>
                </aside>
            </div>
        </div>
    );
}

function Checklist({ title, items, selected, onToggle, emptyText }) {
    return (
        <div className="rounded-lg border border-slate-200">
            <div className="border-b border-slate-200 bg-slate-50 px-4 py-3">
                <h2 className="text-sm font-semibold text-slate-800">{title}</h2>
            </div>
            <div className="max-h-96 space-y-1 overflow-y-auto p-3">
                {items.map((item) => (
                    <label key={item.id} className="flex cursor-pointer items-center gap-3 rounded-md px-3 py-2 text-sm hover:bg-slate-50">
                        <input
                            type="checkbox"
                            checked={selected.includes(item.id)}
                            onChange={() => onToggle(item.id)}
                            className="rounded border-slate-300 text-blue-700 focus:ring-blue-700"
                        />
                        <span className="text-slate-700">{item.nom}</span>
                    </label>
                ))}

                {items.length === 0 && (
                    <p className="px-3 py-8 text-center text-sm text-slate-500">{emptyText}</p>
                )}
            </div>
        </div>
    );
}

FormateurAssignments.layout = (page) => <MainLayout>{page}</MainLayout>;
