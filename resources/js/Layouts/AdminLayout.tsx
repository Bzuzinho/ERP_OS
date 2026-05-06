import type { PageProps } from '@/types';
import AppCard from '@/Components/App/AppCard';
import AppShell from '@/Components/App/AppShell';
import PageHeader from '@/Components/App/PageHeader';
import { Head, Link, usePage } from '@inertiajs/react';
import { type PropsWithChildren, type ReactNode, useEffect, useState } from 'react';

type AdminLayoutProps = PropsWithChildren<{
    title: string;
    subtitle?: string;
    headerActions?: ReactNode;
}>;

const iconClass = 'h-5 w-5';

const iconDashboard = (
    <svg viewBox="0 0 24 24" className={iconClass} fill="none" stroke="currentColor" strokeWidth="1.8" aria-hidden="true">
        <path d="M4 4h7v7H4z" />
        <path d="M13 4h7v4h-7z" />
        <path d="M13 10h7v10h-7z" />
        <path d="M4 13h7v7H4z" />
    </svg>
);

const iconTicket = (
    <svg viewBox="0 0 24 24" className={iconClass} fill="none" stroke="currentColor" strokeWidth="1.8" aria-hidden="true">
        <path d="M4 8.5a2.5 2.5 0 012.5-2.5h11A2.5 2.5 0 0120 8.5v2a2 2 0 000 4v2a2.5 2.5 0 01-2.5 2.5h-11A2.5 2.5 0 014 16.5v-2a2 2 0 000-4z" />
    </svg>
);

const iconCalendar = (
    <svg viewBox="0 0 24 24" className={iconClass} fill="none" stroke="currentColor" strokeWidth="1.8" aria-hidden="true">
        <rect x="3" y="5" width="18" height="16" rx="2" />
        <path d="M8 3v4" />
        <path d="M16 3v4" />
        <path d="M3 10h18" />
    </svg>
);

const iconTask = (
    <svg viewBox="0 0 24 24" className={iconClass} fill="none" stroke="currentColor" strokeWidth="1.8" aria-hidden="true">
        <path d="M9 11l2 2 4-4" />
        <rect x="3" y="4" width="18" height="16" rx="2" />
    </svg>
);

const iconUsers = (
    <svg viewBox="0 0 24 24" className={iconClass} fill="none" stroke="currentColor" strokeWidth="1.8" aria-hidden="true">
        <path d="M16 19a4 4 0 00-8 0" />
        <circle cx="12" cy="11" r="3" />
        <path d="M22 19a4 4 0 00-3-3.87" />
        <path d="M2 19a4 4 0 013-3.87" />
    </svg>
);

const iconFolder = (
    <svg viewBox="0 0 24 24" className={iconClass} fill="none" stroke="currentColor" strokeWidth="1.8" aria-hidden="true">
        <path d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
    </svg>
);

const iconMinutes = (
    <svg viewBox="0 0 24 24" className={iconClass} fill="none" stroke="currentColor" strokeWidth="1.8" aria-hidden="true">
        <path d="M7 4h10" />
        <rect x="5" y="4" width="14" height="16" rx="2" />
        <path d="M9 9h6" />
        <path d="M9 13h6" />
    </svg>
);

const iconBuilding = (
    <svg viewBox="0 0 24 24" className={iconClass} fill="none" stroke="currentColor" strokeWidth="1.8" aria-hidden="true">
        <path d="M4 20h16" />
        <path d="M6 20V7l6-3 6 3v13" />
        <path d="M9 10h1" />
        <path d="M14 10h1" />
        <path d="M9 14h1" />
        <path d="M14 14h1" />
    </svg>
);

const iconArchive = (
    <svg viewBox="0 0 24 24" className={iconClass} fill="none" stroke="currentColor" strokeWidth="1.8" aria-hidden="true">
        <rect x="3" y="4" width="18" height="4" rx="1" />
        <path d="M5 8h14v11a2 2 0 01-2 2H7a2 2 0 01-2-2z" />
    </svg>
);

const iconSettings = (
    <svg viewBox="0 0 24 24" className={iconClass} fill="none" stroke="currentColor" strokeWidth="1.8" aria-hidden="true">
        <circle cx="12" cy="12" r="3" />
        <path d="M19.4 15a1.7 1.7 0 00.34 1.87l.06.06a2 2 0 01-2.83 2.83l-.06-.06a1.7 1.7 0 00-1.87-.34 1.7 1.7 0 00-1 1.56V21a2 2 0 01-4 0v-.09a1.7 1.7 0 00-1-1.56 1.7 1.7 0 00-1.87.34l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.7 1.7 0 005 15a1.7 1.7 0 00-1.56-1H3.3a2 2 0 010-4h.09A1.7 1.7 0 005 8.44a1.7 1.7 0 00-.34-1.87l-.06-.06a2 2 0 012.83-2.83l.06.06A1.7 1.7 0 009.44 4a1.7 1.7 0 001-1.56V2.3a2 2 0 014 0v.09a1.7 1.7 0 001 1.56 1.7 1.7 0 001.87-.34l.06-.06a2 2 0 012.83 2.83l-.06.06A1.7 1.7 0 0019 8.44a1.7 1.7 0 001.56 1h.14a2 2 0 010 4h-.09A1.7 1.7 0 0019.4 15z" />
    </svg>
);

const desktopNavigationItems = [
    { label: 'Dashboard', href: route('admin.dashboard'), activePatterns: ['admin.dashboard'], icon: iconDashboard },
    { label: 'Pedidos', href: route('admin.tickets.index'), activePatterns: ['admin.tickets.*'], icon: iconTicket },
    { label: 'Munícipes / Entidades', href: route('admin.contacts.index'), activePatterns: ['admin.contacts.*'], icon: iconUsers },
    { label: 'Agenda', href: route('admin.events.index'), activePatterns: ['admin.events.*'], icon: iconCalendar },
    { label: 'Tarefas', href: route('admin.tasks.index'), activePatterns: ['admin.tasks.*'], icon: iconTask },
    { label: 'Documentos', href: route('admin.documents.index'), activePatterns: ['admin.documents.*'], icon: iconFolder },
    { label: 'Atas', href: route('admin.meeting-minutes.index'), activePatterns: ['admin.meeting-minutes.*'], icon: iconMinutes },
    { label: 'Espaços', href: route('admin.spaces.index'), activePatterns: ['admin.spaces.*', 'admin.space-*'], icon: iconBuilding },
    {
        label: 'Recursos Materiais',
        href: route('admin.inventory-items.index'),
        activePatterns: ['admin.inventory*'],
        icon: iconArchive,
    },
    { label: 'Recursos Humanos', href: route('admin.hr.employees.index'), activePatterns: ['admin.hr.*'], icon: iconUsers },
    { label: 'Planeamento', href: route('admin.operational-plans.index'), activePatterns: ['admin.operational-plans.*'], icon: iconCalendar },
    { label: 'Relatórios', href: route('admin.reports.index'), activePatterns: ['admin.reports.*'], icon: iconDashboard },
    { label: 'Configurações', href: route('admin.settings.index'), activePatterns: ['admin.settings.*'], icon: iconSettings },
];

const mobileNavigationItems = [
    { label: 'Dashboard', href: route('admin.dashboard'), activePatterns: ['admin.dashboard'], icon: iconDashboard },
    { label: 'Pedidos', href: route('admin.tickets.index'), activePatterns: ['admin.tickets.*'], icon: iconTicket },
    { label: 'Agenda', href: route('admin.events.index'), activePatterns: ['admin.events.*'], icon: iconCalendar },
    { label: 'Tarefas', href: route('admin.tasks.index'), activePatterns: ['admin.tasks.*'], icon: iconTask },
    { label: 'Mais', href: route('admin.more.index'), activePatterns: ['admin.more.*'], icon: iconSettings },
];

const detailBackRoutes: Record<string, string> = {
    'Admin/Tickets/': 'admin.tickets.index',
    'Admin/Events/': 'admin.events.index',
    'Admin/Tasks/': 'admin.tasks.index',
    'Admin/Documents/': 'admin.documents.index',
    'Admin/Spaces/': 'admin.spaces.index',
    'Admin/Settings/Users/': 'admin.settings.users.index',
    'Admin/Settings/Roles/': 'admin.settings.roles.index',
};

export default function AdminLayout({
    children,
    title,
    subtitle,
    headerActions,
}: AdminLayoutProps) {
    const { auth, flash } = usePage<PageProps>().props;
    const page = usePage<PageProps>();
    const user = auth.user;
    const [flashVisible, setFlashVisible] = useState(true);
    const isDetailPage = /\/(Show|Edit|Create)$/.test(page.component);

    let mobileBackHref = route('admin.dashboard');
    for (const [componentPrefix, routeName] of Object.entries(detailBackRoutes)) {
        if (page.component.startsWith(componentPrefix)) {
            mobileBackHref = route(routeName);
            break;
        }
    }

    useEffect(() => {
        setFlashVisible(true);
        const timer = setTimeout(() => setFlashVisible(false), 5000);
        return () => clearTimeout(timer);
    }, [flash]);

    return (
        <AppShell
            title={title}
            subtitle={subtitle}
            organizationLabel={user?.organization?.name ?? 'Junta de Freguesia Demo'}
            organizationLogoUrl={user?.organization?.logo_path ? `/storage/${user.organization.logo_path}` : null}
            desktopNav={desktopNavigationItems}
            mobileNav={mobileNavigationItems}
            showBackOnMobile={isDetailPage}
            mobileBackHref={mobileBackHref}
        >
            <Head title={title} />
            <div className="mb-4 hidden lg:block">
                <PageHeader
                    title={title}
                    subtitle={subtitle}
                    actions={
                        <div className="flex items-center gap-2">
                            {auth.can.accessAdmin ? (
                                <Link href={route('portal.dashboard')} className="rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                    Portal
                                </Link>
                            ) : null}
                            {headerActions}
                        </div>
                    }
                />
            </div>

            <div className="mb-4 lg:hidden">{headerActions}</div>

            {flash?.success && flashVisible ? (
                <AppCard className="mb-4 border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700">
                    <div className="flex items-start justify-between gap-2">
                        <span>{flash.success}</span>
                        <button onClick={() => setFlashVisible(false)} className="text-emerald-600 hover:text-emerald-800">x</button>
                    </div>
                </AppCard>
            ) : null}

            {flash?.error && flashVisible ? (
                <AppCard className="mb-4 border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
                    <div className="flex items-start justify-between gap-2">
                        <span>{flash.error}</span>
                        <button onClick={() => setFlashVisible(false)} className="text-rose-600 hover:text-rose-800">x</button>
                    </div>
                </AppCard>
            ) : null}

            {children}
        </AppShell>
    );
}