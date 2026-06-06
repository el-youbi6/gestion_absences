import { useEffect, useMemo } from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import { CalendarDays, Save } from 'lucide-react';
import { ToastContainer, toast } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';
import MainLayout from '../../Layouts/MainLayout';
import YearSelect from '../../Components/YearSelect';
import AbsenceSelectField from '../../Components/AbsenceSelectField';
import AbsenceTimeSelect from '../../Components/AbsenceTimeSelect';
import StagiairePresenceTable from '../../Components/StagiairePresenceTable';

export default function SaisieAbsences({ filieres, modules, groupes, timeSlots, today }) {
    const { flash } = usePage().props;
    const { data, setData, post, processing, errors, reset } = useForm({
        filiere_id: '',
        module_id: '',
        groupe_id: '',
        date: today,
        heure_debut: '',
        heure_fin: '',
        presences: [],
    });

    useEffect(() => {
        if (flash?.success) {
            toast.success(flash.success);
        }

        if (flash?.error) {
            toast.error(flash.error);
        }
    }, [flash?.success, flash?.error]);

    const filteredModules = useMemo(() => {
        return modules.filter((module) => String(module.filiere_id) === String(data.filiere_id));
    }, [modules, data.filiere_id]);

    const filteredGroupes = useMemo(() => {
        return groupes.filter((groupe) => String(groupe.filiere_id) === String(data.filiere_id));
    }, [groupes, data.filiere_id]);

    const selectedGroupe = useMemo(() => {
        return groupes.find((groupe) => String(groupe.id) === String(data.groupe_id));
    }, [groupes, data.groupe_id]);

    useEffect(() => {
        if (!selectedGroupe) {
            setData('presences', []);
            return;
        }

        setData(
            'presences',
            selectedGroupe.stagiaires
                .filter((stagiaire) => stagiaire.latest_absence && !stagiaire.latest_absence.is_justified)
                .map((stagiaire) => ({
                    stagiaire_id: stagiaire.id,
                })),
        );
    }, [data.groupe_id]);

    const updatePresence = (stagiaireId, isAbsent) => {
        if (isAbsent) {
            if (data.presences.some((presence) => presence.stagiaire_id === stagiaireId)) {
                return;
            }

            setData('presences', [...data.presences, { stagiaire_id: stagiaireId }]);
            return;
        }

        setData(
            'presences',
            data.presences.filter((presence) => presence.stagiaire_id !== stagiaireId),
        );
    };

    const handleFiliereChange = (value) => {
        setData({
            ...data,
            filiere_id: value,
            module_id: '',
            groupe_id: '',
            presences: [],
        });
    };

    const handleSubmit = (event) => {
        event.preventDefault();

        post('/absences/saisie', {
            preserveScroll: true,
            onSuccess: () => {
                reset('module_id', 'groupe_id', 'heure_debut', 'heure_fin', 'presences');
            },
        });
    };

    return (
        <div className="min-h-screen bg-slate-50">
            <Head title="Saisie absences" />
            <ToastContainer position="top-right" autoClose={3500} />

            <div className="mb-8 flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p className="text-sm font-semibold uppercase tracking-[0.18em] text-emerald-700">
                        Formateur
                    </p>
                    <h1 className="mt-2 text-3xl font-bold text-slate-900">
                        Saisie des absences
                    </h1>
                    <p className="mt-2 text-slate-500">
                        Selectionnez la seance puis cochez uniquement les stagiaires absents.
                    </p>
                </div>
                <YearSelect />
            </div>

            <form onSubmit={handleSubmit} className="space-y-6">
                <section className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <div className="grid gap-5 md:grid-cols-2 xl:grid-cols-5">
                        <AbsenceSelectField
                            label="Filiere"
                            value={data.filiere_id}
                            onChange={handleFiliereChange}
                            options={filieres}
                            placeholder="Choisir une filiere"
                            error={errors.filiere_id}
                        />

                        <AbsenceSelectField
                            label="Module"
                            value={data.module_id}
                            onChange={(value) => setData('module_id', value)}
                            options={filteredModules}
                            placeholder="Choisir un module"
                            error={errors.module_id}
                            disabled={!data.filiere_id}
                        />

                        <AbsenceSelectField
                            label="Groupe"
                            value={data.groupe_id}
                            onChange={(value) => setData('groupe_id', value)}
                            options={filteredGroupes}
                            placeholder="Choisir un groupe"
                            error={errors.groupe_id}
                            disabled={!data.filiere_id}
                        />

                        <AbsenceTimeSelect
                            label="Heure debut"
                            value={data.heure_debut}
                            onChange={(value) => setData('heure_debut', value)}
                            options={timeSlots.debut}
                            error={errors.heure_debut}
                        />

                        <AbsenceTimeSelect
                            label="Heure fin"
                            value={data.heure_fin}
                            onChange={(value) => setData('heure_fin', value)}
                            options={timeSlots.fin}
                            error={errors.heure_fin}
                        />
                    </div>

                    <label className="mt-5 block max-w-xs">
                        <span className="mb-2 block text-sm font-semibold text-slate-700">
                            Date
                        </span>
                        <div className="relative">
                            <CalendarDays className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                            <input
                                type="date"
                                value={data.date}
                                onChange={(event) => setData('date', event.target.value)}
                                className="w-full rounded-lg border border-slate-200 bg-white py-3 pl-10 pr-4 text-sm text-slate-800 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                            />
                        </div>
                        {errors.date && (
                            <span className="mt-2 block text-sm text-red-600">{errors.date}</span>
                        )}
                    </label>
                </section>

                <section className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 className="text-lg font-bold text-slate-900">
                                Liste des stagiaires
                            </h2>
                            <p className="text-sm text-slate-500">
                                Les stagiaires non coches ne generent aucune absence.
                            </p>
                        </div>

                        <button
                            type="submit"
                            disabled={processing || !selectedGroupe}
                            className="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:bg-slate-400"
                        >
                            <Save className="h-4 w-4" />
                            {processing ? 'Enregistrement...' : 'Enregistrer'}
                        </button>
                    </div>

                    {selectedGroupe ? (
                        <StagiairePresenceTable
                            stagiaires={selectedGroupe.stagiaires}
                            presences={data.presences}
                            onChange={updatePresence}
                            errors={errors}
                        />
                    ) : (
                        <div className="rounded-lg border border-dashed border-slate-300 bg-slate-50 px-5 py-10 text-center text-sm text-slate-500">
                            Choisissez un groupe pour afficher ses stagiaires.
                        </div>
                    )}
                </section>
            </form>
        </div>
    );
}

SaisieAbsences.layout = (page) => <MainLayout>{page}</MainLayout>;
