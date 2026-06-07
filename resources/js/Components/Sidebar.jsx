import { Link, usePage } from '@inertiajs/react';
import { BarChart3, BookUser, CheckSquare, FileText, Import, LogOut, UserRoundCog, UsersRound } from 'lucide-react';
import OFPPT from "../assets/OFPPT2.webp";

const sidebarItems = [
    {
        name: 'Import',
        href: '/import',
        icon: <Import className='w-4' />,
        roles: ['admin', 'surveillant'],
    },
    {
        name: 'Utilisateurs',
        href: '/admin/users',
        icon: <UsersRound className='w-4' />,
        roles: ['admin'],
    },
    {
        name: 'Affectations',
        href: '/admin/affectations-formateurs',
        icon: <BookUser className='w-4' />,
        roles: ['admin', 'surveillant'],
    },
    {
        name: 'Statistiques',
        href: '/admin/statistiques-absences',
        icon: <BarChart3 className='w-4' />,
        roles: ['admin', 'surveillant'],
    },
    {
        name: 'Justifications',
        href: '/admin/justification-absences',
        icon: <CheckSquare className='w-4' />,
        roles: ['admin', 'surveillant'],
    },
    {
        name: 'Saisie absences',
        href: '/absences/saisie',
        icon: <UserRoundCog className='w-4' />,
        roles: ['formateur'],
    },
    {
        name: 'Rapport modules',
        href: '/absences/rapport-modules',
        icon: <FileText className='w-4' />,
        roles: ['formateur'],
    },
];

export default function Sidebar() {
    const { auth } = usePage().props;
    const user = auth.user;
    const role = user.role;
    const Items = sidebarItems.filter((item) => item.roles.includes(role));
    const currentPath = typeof window !== 'undefined' ? window.location.pathname : '';
    const isActive = (href) => currentPath === href || currentPath.startsWith(`${href}/`);
    const fullName = [user.prenom, user.nom].filter(Boolean).join(' ') || user.email || 'Utilisateur';
    const initials = fullName
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase())
        .join('');

    return (
        <aside className="flex min-h-screen w-64 flex-col border-r border-slate-200 bg-white px-4 py-6">
            <div className="mb-4">
                <img
                    src={OFPPT}
                    alt="CHU Hassan II"
                    className="w-[100px] mx-auto object-contain"
                />
            </div>

            <nav className="flex-1 space-y-1">
                {Items.map((item) => {
                    const active = isActive(item.href);
                    return (
                        <Link
                            key={item.name}
                            href={item.href}
                            className={`flex gap-2 rounded-2xl px-4 py-3 text-sm font-medium transition ${active ? 'bg-slate-100 text-slate-900 font-semibold' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900'}`}
                        >
                            {item.icon}
                            {item.name}
                        </Link>
                    );
                })}

                <Link
                    href="/logout"
                    method="post"
                    as="button"
                    className="flex gap-2 w-full text-left rounded-2xl px-4 py-3 text-sm font-medium text-red-700 transition hover:bg-red-50 hover:text-red-900"
                >
                    <LogOut className='w-4' />
                    Deconnexion
                </Link>
            </nav>

            <div className="mt-6 border-t border-slate-200 pt-4">
                <div className="flex items-center gap-3 rounded-2xl bg-slate-50 px-3 py-3">
                    <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-slate-900 text-sm font-semibold text-white">
                        {initials || 'U'}
                    </div>
                    <div className="min-w-0">
                        <p className="truncate text-sm font-semibold text-slate-900">{fullName}</p>
                        {user.email && <p className="truncate text-xs text-slate-500">{user.email}</p>}
                    </div>
                </div>
            </div>
        </aside>
    );
}
