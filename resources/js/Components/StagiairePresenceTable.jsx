import { X } from 'lucide-react';

export default function StagiairePresenceTable({ stagiaires, presences, onChange, errors }) {
    if (!stagiaires.length) {
        return (
            <div className="rounded-lg border border-dashed border-slate-300 bg-slate-50 px-5 py-8 text-center text-sm text-slate-500">
                Aucun stagiaire trouve pour ce groupe.
            </div>
        );
    }

    const isAbsent = (stagiaireId) => {
        return presences.some((presence) => presence.stagiaire_id === stagiaireId);
    };

    return (
        
        <div className="overflow-x-auto">
            <table className="min-w-full divide-y divide-slate-200">
                <thead className="bg-slate-50">
                    <tr>
                        <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">N </th>
                        <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Nom </th>
                        <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Prenom </th>
                        <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">CIN </th>
                        <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Absence</th>
                    </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                    {stagiaires.map((stagiaire, i) => {
                        const absent = isAbsent(stagiaire.id);
                        return(
                        <tr key={stagiaire.id} className="hover:bg-slate-50">
                            <td className="whitespace-nowrap px-4 py-3 text-sm text-slate-700">{i + 1}</td>
                            <td className="whitespace-nowrap px-4 py-3 text-sm text-slate-700">{stagiaire.nom}</td>
                            <td className="whitespace-nowrap px-4 py-3 text-sm text-slate-700">{stagiaire.prenom}</td>
                            <td className="whitespace-nowrap px-4 py-3 text-sm text-slate-700">{stagiaire.cin}</td>
                            <td className="whitespace-nowrap px-4 py-3 text-sm text-slate-700 ">
                                <label className="">
                                    <input
                                        type="checkbox"
                                        checked={absent}
                                        onChange={(event) => onChange(stagiaire.id, event.target.checked)}
                                        className="peer sr-only"
                                    />
                                    <span className="flex h-9 w-9 cursor-pointer items-center justify-center rounded-lg border border-slate-200 text-slate-400 transition peer-checked:border-red-600 peer-checked:bg-red-600 peer-checked:text-white">
                                        <X className="h-4 w-4" />
                                    </span>
                                </label>
                            </td>
                        </tr>)
                    })}
                </tbody>
            </table>
            {errors?.presences && (
                <p className="border-t border-red-100 bg-red-50 px-4 py-3 text-sm text-red-600">
                    {errors.presences}
                </p>
            )}
        </div>
        
    );
}
