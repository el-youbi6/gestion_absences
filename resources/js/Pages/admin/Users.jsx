import React, { useEffect, useMemo, useState } from "react";
import { Head, Link, router, useForm, usePage } from "@inertiajs/react";
import { Edit3, Plus, Search, Trash2, X } from "lucide-react";
import { ToastContainer, toast } from "react-toastify";
import "react-toastify/dist/ReactToastify.css";
import MainLayout from "../../Layouts/MainLayout";

const roles = [
    { value: "surveillant", label: "Surveillant" },
    { value: "stagiaire", label: "Stagiaire" },
    { value: "formateur", label: "Formateur" },
];

const emptyUser = {
    nom: "",
    prenom: "",
    cin: "",
    email: "",
    role: "surveillant",
    password: "",
    groupe_id: "",
};

export default function Users({ users, filters, groupes }) {
    const { flash } = usePage().props;
    const [editingUser, setEditingUser] = useState(null);
    const [cin, setCin] = useState(filters.cin || "");
    const [role, setRole] = useState(filters.role || "");

    const { data, setData, post, put, processing, errors, reset, clearErrors } = useForm(emptyUser);

    useEffect(() => {
        if (flash.success) toast.success(flash.success);
        if (flash.error) toast.error(flash.error);
    }, [flash?.success, flash?.error]);

    const title = useMemo(() => editingUser ? "Modifier utilisateur" : "Ajouter utilisateur", [editingUser]);

    const startCreate = () => {
        setEditingUser(null);
        clearErrors();
        reset();
    };

    const startEdit = (user) => {
        setEditingUser(user);
        clearErrors();
        setData({
            nom: user.nom || "",
            prenom: user.prenom || "",
            cin: user.cin || "",
            email: user.email || "",
            role: user.role || "surveillant",
            password: "",
            groupe_id: user.groupe_id || "",
        });
    };

    const submit = (event) => {
        event.preventDefault();

        if (editingUser) {
            put(`/admin/users/${editingUser.id}`, {
                preserveScroll: true,
                onSuccess: startCreate,
            });
            return;
        }

        post("/admin/users", {
            preserveScroll: true,
            onSuccess: startCreate,
        });
    };

    const search = (event) => {
        event.preventDefault();

        router.get("/admin/users", { cin, role }, {
            preserveState: true,
            replace: true,
        });
    };

    const remove = (user) => {
        if (window.confirm(`Supprimer ${user.nom} ${user.prenom} ?`)) {
            router.delete(`/admin/users/${user.id}`, { preserveScroll: true });
        }
    };

    return (
        <div className="space-y-6">
            <Head title="Utilisateurs" />
            <ToastContainer position="top-right" autoClose={3500} />

            <div className="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <h1 className="text-3xl font-bold text-slate-900">Gestion des utilisateurs</h1>
                    <p className="mt-1 text-sm text-slate-500">Surveillants, stagiaires et formateurs.</p>
                </div>

                <form onSubmit={search} className="flex flex-col gap-2 sm:flex-row">
                    <select
                        value={role}
                        onChange={(event) => setRole(event.target.value)}
                        className="rounded-lg border-slate-300 text-sm"
                    >
                        <option value="">Tous les roles</option>
                        {roles.map((item) => (
                            <option key={item.value} value={item.value}>{item.label}</option>
                        ))}
                    </select>

                    <div className="relative">
                        <Search className="pointer-events-none absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
                        <input
                            value={cin}
                            onChange={(event) => setCin(event.target.value)}
                            placeholder="Recherche CIN"
                            className="w-full rounded-lg border-slate-300 pl-9 text-sm"
                        />
                    </div>

                    <button className="inline-flex items-center justify-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">
                        <Search className="h-4 w-4" />
                        Rechercher
                    </button>
                </form>
            </div>

            <div className="grid gap-6 xl:grid-cols-[380px_1fr]">
                <section className="rounded-lg border border-slate-200 bg-white p-5">
                    <div className="mb-4 flex items-center justify-between">
                        <h2 className="text-lg font-semibold text-slate-900">{title}</h2>
                        {editingUser && (
                            <button onClick={startCreate} className="rounded-md p-2 text-slate-500 hover:bg-slate-100" type="button">
                                <X className="h-4 w-4" />
                            </button>
                        )}
                    </div>

                    <form onSubmit={submit} className="space-y-4">
                        <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
                            <Field label="Nom" value={data.nom} onChange={(value) => setData("nom", value)} error={errors.nom} />
                            <Field label="Prenom" value={data.prenom} onChange={(value) => setData("prenom", value)} error={errors.prenom} />
                            <Field label="CIN" value={data.cin} onChange={(value) => setData("cin", value)} error={errors.cin} />
                            <Field label="Email" type="email" value={data.email} onChange={(value) => setData("email", value)} error={errors.email} />
                        </div>

                        <div>
                            <label className="mb-1 block text-sm font-medium text-slate-700">Role</label>
                            <select
                                value={data.role}
                                onChange={(event) => setData("role", event.target.value)}
                                className="w-full rounded-lg border-slate-300 text-sm"
                            >
                                {roles.map((item) => (
                                    <option key={item.value} value={item.value}>{item.label}</option>
                                ))}
                            </select>
                            {errors.role && <p className="mt-1 text-sm text-red-600">{errors.role}</p>}
                        </div>

                        {data.role === "stagiaire" && (
                            <div>
                                <label className="mb-1 block text-sm font-medium text-slate-700">Groupe</label>
                                <select
                                    value={data.groupe_id}
                                    onChange={(event) => setData("groupe_id", event.target.value)}
                                    className="w-full rounded-lg border-slate-300 text-sm"
                                >
                                    <option value="">Choisir un groupe</option>
                                    {groupes.map((groupe) => (
                                        <option key={groupe.id} value={groupe.id}>{groupe.nom}</option>
                                    ))}
                                </select>
                                {errors.groupe_id && <p className="mt-1 text-sm text-red-600">{errors.groupe_id}</p>}
                            </div>
                        )}

                        <Field
                            label={editingUser ? "Nouveau mot de passe" : "Mot de passe"}
                            type="password"
                            value={data.password}
                            onChange={(value) => setData("password", value)}
                            error={errors.password}
                        />

                        <button
                            disabled={processing}
                            className="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white disabled:bg-slate-400"
                        >
                            <Plus className="h-4 w-4" />
                            {editingUser ? "Enregistrer" : "Ajouter"}
                        </button>
                    </form>
                </section>

                <section className="overflow-hidden rounded-lg border border-slate-200 bg-white">
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-slate-200">
                            <thead className="bg-slate-50">
                                <tr>
                                    <Th>Nom complet</Th>
                                    <Th>CIN</Th>
                                    <Th>Email</Th>
                                    <Th>Role</Th>
                                    <Th>Groupe</Th>
                                    <Th>Actions</Th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {users.data.map((user) => (
                                    <tr key={user.id} className="hover:bg-slate-50">
                                        <Td>{user.nom} {user.prenom}</Td>
                                        <Td>{user.cin}</Td>
                                        <Td>{user.email}</Td>
                                        <Td><span className="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">{user.role}</span></Td>
                                        <Td>{user.groupe || "-"}</Td>
                                        <Td>
                                            <div className="flex gap-2">
                                                <button onClick={() => startEdit(user)} className="rounded-md p-2 text-blue-700 hover:bg-blue-50" type="button">
                                                    <Edit3 className="h-4 w-4" />
                                                </button>
                                                <button onClick={() => remove(user)} className="rounded-md p-2 text-red-700 hover:bg-red-50" type="button">
                                                    <Trash2 className="h-4 w-4" />
                                                </button>
                                            </div>
                                        </Td>
                                    </tr>
                                ))}

                                {users.data.length === 0 && (
                                    <tr>
                                        <td colSpan="6" className="px-4 py-10 text-center text-sm text-slate-500">
                                            Aucun utilisateur trouve.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                    <Pagination links={users.links} meta={`${users.from || 0}-${users.to || 0} / ${users.total}`} />
                </section>
            </div>
        </div>
    );
}

function Field({ label, value, onChange, error, type = "text" }) {
    return (
        <div>
            <label className="mb-1 block text-sm font-medium text-slate-700">{label}</label>
            <input
                type={type}
                value={value}
                onChange={(event) => onChange(event.target.value)}
                className="w-full rounded-lg border-slate-300 text-sm"
            />
            {error && <p className="mt-1 text-sm text-red-600">{error}</p>}
        </div>
    );
}

function Th({ children }) {
    return <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">{children}</th>;
}

function Td({ children }) {
    return <td className="whitespace-nowrap px-4 py-3 text-sm text-slate-700">{children}</td>;
}

function Pagination({ links, meta }) {
    return (
        <div className="flex flex-col gap-3 border-t border-slate-200 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
            <p className="text-sm text-slate-500">{meta}</p>
            <div className="flex flex-wrap gap-1">
                {links.map((link, index) => (
                    <Link
                        key={`${link.label}-${index}`}
                        href={link.url || "#"}
                        preserveScroll
                        className={`rounded-md px-3 py-1.5 text-sm ${
                            link.active
                                ? "bg-slate-900 text-white"
                                : link.url
                                    ? "bg-white text-slate-700 hover:bg-slate-100"
                                    : "cursor-not-allowed text-slate-300"
                        }`}
                        dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                ))}
            </div>
        </div>
    );
}

Users.layout = (page) => <MainLayout>{page}</MainLayout>;
