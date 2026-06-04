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
        <div className="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <div className="grid grid-cols-[1fr_150px] bg-slate-100 px-4 py-3 text-xs font-bold uppercase text-slate-500">
                <span>Stagiaire</span>
                <span className="text-center">Absence</span>
            </div>

            <div className="divide-y divide-slate-100">
                {stagiaires.map((stagiaire) => {
                    const absent = isAbsent(stagiaire.id);

                    return (
                        <div
                            key={stagiaire.id}
                            className="grid grid-cols-[1fr_150px] items-center px-4 py-3"
                        >
                            <div>
                                <p className="font-semibold text-slate-800">
                                    {stagiaire.nom} {stagiaire.prenom}
                                </p>
                                <p className="text-sm text-slate-500">{stagiaire.cin}</p>
                            </div>

                            <label className="flex justify-center">
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
                        </div>
                    );
                })}
            </div>

            {errors?.presences && (
                <p className="border-t border-red-100 bg-red-50 px-4 py-3 text-sm text-red-600">
                    {errors.presences}
                </p>
            )}
        </div>
    );
}
