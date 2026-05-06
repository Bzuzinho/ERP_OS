import type { PageProps } from '@/types';
import AppCard from '@/Components/App/AppCard';
import AppShell from '@/Components/App/AppShell';
import PageHeader from '@/Components/App/PageHeader';
import { Head, usePage } from '@inertiajs/react';
import { type PropsWithChildren, type ReactNode, useEffect, useState } from 'react';

type PortalLayoutProps = PropsWithChildren<{
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

const iconFolder = (
    <svg viewBox="0 0 24 24" className={iconClass} fill="none" stroke="currentColor" strokeWidth="1.8" aria-hidden="true">
        <path d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
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

const iconSettings = (
    <svg viewBox="0 0 24 24" className={iconClass} fill="none" stroke="currentColor" strokeWidth="1.8" aria-hidden="true">
        <circle cx="12" cy="12" r="3" />
        <path d="M19.4 15a1.7 1.7 0 00.34 1.87l.06.06a2 2 0 01-2.83 2.83l-.06-.06a1.7 1.7 0 00-1.87-.34 1.7 1.7 0 00-1 1.56V21a2 2 0 01-4 0v-.09a1.7 1.7 0 00-1-1.56 1.7 1.7 0 00-1.87.34l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.7 1.7 0 005 15a1.7 1.7 0 00-1.56-1H3.3a2 2 0 010-4h.09A1.7 1.7 0 005 8.44a1.7 1.7 0 00-.34-1.87l-.06-.06a2 2 0 012.83-2.83l.06.06A1.7 1.7 0 009.44 4a1.7 1.7 0 001-1.56V2.3a2 2 0 014 0v.09a1.7 1.7 0 001 1.56 1.7 1.7 0 001.87-.34l.06-.06a2 2 0 012.83 2.83l-.06.06A1.7 1.7 0 0019 8.44a1.7 1.7 0 001.56 1h.14a2 2 0 010 4h-.09A1.7 1.7 0 0019.4 15z" />
    </svg>
);

const desktopNavigationItems = [
    { label: 'Início', href: route('portal.dashboard'), activePatterns: ['portal.dashboard'], icon: iconDashboard },
    { label: 'Pedidos', href: route('portal.tickets.index'), activePatterns: ['portal.tickets.*'], icon: iconTicket },
    { label: 'Agenda', href: route('portal.events.index'), activePatterns: ['portal.events.*'], icon: iconCalendar },
    { label: 'Documentos', href: route('portal.documents.index'), activePatterns: ['portal.documents.*'], icon: iconFolder },
    {
        label: 'Reservas',
        href: route('portal.space-reservations.index'),
        activePatterns: ['portal.space-reservations.*', 'portal.spaces.*'],
        icon: iconBuilding,
    },
    { label: 'Alertas', href: route('portal.notifications.index'), activePatterns: ['portal.notifications.*'], icon: iconSettings },
    { label: 'Perfil', href: route('profile.edit'), activePatterns: ['profile.edit'], icon: iconSettings },
    { label: 'Mais', href: route('portal.more.index'), activePatterns: ['portal.more.*'], icon: iconSettings },
];

const mobileNavigationItems = [
    { label: 'Início', href: route('portal.dashboard'), activePatterns: ['portal.dashboard'], icon: iconDashboard },
    { label: 'Pedidos', href: route('portal.tickets.index'), activePatterns: ['portal.tickets.*'], icon: iconTicket },
    { label: 'Agenda', href: route('portal.events.index'), activePatterns: ['portal.events.*'], icon: iconCalendar },
    { label: 'Alertas', href: route('portal.notifications.index'), activePatterns: ['portal.notifications.*'], icon: iconSettings },
    { label: 'Mais', href: route('portal.more.index'), activePatterns: ['portal.more.*'], icon: iconSettings },
];

const detailBackRoutes: Record<string, string> = {
    'Portal/Tickets/': 'portal.tickets.index',
    'Portal/Events/': 'portal.events.index',
    'Portal/Documents/': 'portal.documents.index',
    'Portal/SpaceReservations/': 'portal.space-reservations.index',
};

export default function PortalLayout({
    children,
    title,
    subtitle,
    headerActions,
}: PortalLayoutProps) {
    const { auth, flash } = usePage<PageProps>().props;
    const page = usePage<PageProps>();
    const user = auth.user;
    const [flashVisible, setFlashVisible] = useState(true);
    const isDetailPage = /\/(Show|Edit|Create)$/.test(page.component);

    let mobileBackHref = route('portal.dashboard');
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
            organizationHref={route('profile.edit')}
            desktopNav={desktopNavigationItems}
            mobileNav={mobileNavigationItems}
            showBackOnMobile={isDetailPage}
            mobileBackHref={mobileBackHref}
        >
            <Head title={title} />
            <div className="mb-4 hidden lg:block">
                <PageHeader title={title} subtitle={subtitle} actions={<div className="flex items-center gap-2">{headerActions}</div>} />
            </div>

            <div className="mb-4 lg:hidden">{headerActions}</div>

            {flash?.success && flashVisible ? (
                <AppCard className="mb-4 border-blue-200 bg-blue-50 p-4 text-sm text-blue-700">
                    <div className="flex items-start justify-between gap-2">
                        <span>{flash.success}</span>
                        <button onClick={() => setFlashVisible(false)} className="text-blue-600 hover:text-blue-800">x</button>
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
