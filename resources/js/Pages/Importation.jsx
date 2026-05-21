import React from "react";
import { useForm, usePage } from "@inertiajs/react";
import { UploadCloud, FileSpreadsheet } from "lucide-react";

export default function ImportPage() {

    const { flash } = usePage().props;
    const { data, setData, post, processing, errors } = useForm({
        file: null,
        type: "global",
    });

    const importTypes = [
        {
            value: "global",
            title: "Import Global",
            description: "Importer toutes les feuilles Excel",
        },
        {
            value: "stagiaires",
            title: "Import Stagiaires",
            description: "Importer uniquement les stagiaires",
        },
        {
            value: "groupes",
            title: "Import Groupes",
            description: "Importer uniquement les groupes",
        },
    ];

    const handleSubmit = (e) => {
        e.preventDefault();

        post("/import", {
            forceFormData: true,
        });
    };

    return (
        <div className="min-h-screen bg-gray-100 flex items-center justify-center p-6">


            <div className="w-full max-w-4xl bg-white rounded-3xl shadow-xl p-8">

                {/* Header */}
                <div className="mb-8">
                    <h1 className="text-4xl font-bold text-gray-800">
                        Importation Excel
                    </h1>

                    <p className="text-gray-500 mt-2">
                        Importez les données rapidement
                    </p>
                </div>

                {/* Import Types */}
                <div className="grid md:grid-cols-3 gap-5 mb-8">

                    {importTypes.map((type) => (

                        <button
                            key={type.value}
                            type="button"
                            onClick={() => setData("type", type.value)}
                            className={`p-5 rounded-2xl border transition-all text-left ${data.type === type.value
                                    ? "bg-blue-600 text-white border-blue-600 shadow-lg"
                                    : "bg-gray-50 hover:bg-gray-100 border-gray-200"
                                }`}
                        >
                            <h2 className="text-lg font-semibold">
                                {type.title}
                            </h2>

                            <p
                                className={`text-sm mt-2 ${data.type === type.value
                                        ? "text-blue-100"
                                        : "text-gray-500"
                                    }`}
                            >
                                {type.description}
                            </p>

                        </button>
                    ))}
                </div>

                {/* Form */}
                <form onSubmit={handleSubmit}>

                    {/* Upload Box */}
                    <div className="border-2 border-dashed border-gray-300 rounded-3xl p-10 text-center bg-gray-50">

                        <div className="flex justify-center mb-4">
                            <div className="bg-blue-100 p-5 rounded-full">
                                <UploadCloud className="w-12 h-12 text-blue-600" />
                            </div>
                        </div>

                        <h3 className="text-2xl font-semibold text-gray-700">
                            Choisir un fichier Excel
                        </h3>

                        <p className="text-gray-500 mt-2">
                            Format supporté : .xlsx
                        </p>

                        <label className="inline-block mt-6">

                            <input
                                type="file"
                                className="hidden"
                                accept=".xlsx,.xls"
                                onChange={(e) =>
                                    setData("file", e.target.files[0])
                                }
                            />

                            <div className="cursor-pointer bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-medium transition">
                                Sélectionner un fichier
                            </div>
                        </label>

                        {/* File Name */}
                        {data.file && (
                            <div className="mt-6 flex items-center justify-center gap-2 text-gray-700">

                                <FileSpreadsheet className="w-5 h-5 text-green-600" />

                                <span className="font-medium">
                                    {data.file.name}
                                </span>

                            </div>
                        )}

                        {/* Error */}
                        {errors.file && (
                            <p className="text-red-500 mt-4 text-sm">
                                {errors.file}
                            </p>
                        )}
                    </div>

                    {/* Footer */}
                    <div className="mt-8 flex justify-between items-center">

                        <div className="text-sm text-gray-500">
                            Type :
                            <span className="font-semibold ml-2 capitalize">
                                {data.type}
                            </span>
                        </div>

                        <button
                            type="submit"
                            disabled={processing}
                            className={`px-8 py-3 rounded-xl font-semibold text-white transition ${processing
                                    ? "bg-gray-400 cursor-not-allowed"
                                    : "bg-green-600 hover:bg-green-700"
                                }`}
                        >
                            {processing ? "Importation..." : "Importer"}
                        </button>
                        {
                            flash.success && (
                                <div>
                                    {flash.success}
                                </div>
                            )
                        }

                    </div>
                </form>
            </div>
        </div>
    );
}