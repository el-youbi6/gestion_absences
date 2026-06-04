import { Link, usePage } from '@inertiajs/react';
import { BarChart3, FileText, Import, LogOut, UserRoundCog, UsersRound, BookUser } from 'lucide-react';
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
    const role = auth.user.role;
    const Items = sidebarItems.filter((item) => item.roles.includes(role));
    const currentPath = typeof window !== 'undefined' ? window.location.pathname : '';
    const isActive = (href) => currentPath === href || currentPath.startsWith(`${href}/`);

    return (
        <aside className="w-64 border-r border-slate-200 bg-white px-4 py-6">
            <div className="mb-4">
                
                <img
                    src={OFPPT}
                    alt="CHU Hassan II"
                    className="w-[100px] mx-auto object-contain"
                />
            </div>

            <nav className="space-y-1">
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
                    Logout
                </Link>
            </nav>
        </aside>
    );
}
