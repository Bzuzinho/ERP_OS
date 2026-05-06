import AdminLayout from '@/Layouts/AdminLayout';
import AppBadge from '@/Components/App/AppBadge';
import AppCard from '@/Components/App/AppCard';
import { Head, Link } from '@inertiajs/react';

type Props = {
    cards: {
        users: boolean;
        roles: boolean;
        serviceAreas: boolean;
        notifications: boolean;
        organization: boolean;
        generalSettings: boolean;
    };
};

const iconUsers = (
    <svg viewBox="0 0 24 24" className="h-6 w-6" fill="none" stroke="currentColor" strokeWidth="1.8" aria-hidden="true">
        <path d="M16 19a4 4 0 00-8 0" />
        <circle cx="12" cy="11" r="3" />
        <path d="M22 19a4 4 0 00-3-3.87" />
        <path d="M2 19a4 4 0 013-3.87" />
    </svg>
);

const iconShield = (
    <svg viewBox="0 0 24 24" className="h-6 w-6" fill="none" stroke="currentColor" strokeWidth="1.8" aria-hidden="true">
        <path d="M12 2l8 4v6c0 5-3.6 9.7-8 11-4.4-1.3-8-6-8-11V6z" />
        <path d="M9 12l2 2 4-4" />
    </svg>
);

const iconBuilding = (
    <svg viewBox="0 0 24 24" className="h-6 w-6" fill="none" stroke="currentColor" strokeWidth="1.8" aria-hidden="true">
        <path d="M4 20h16" />
        <path d="M6 20V7l6-3 6 3v13" />
        <path d="M9 10h1" /><path d="M14 10h1" />
    </svg>
);

const iconSliders = (
    <svg viewBox="0 0 24 24" className="h-6 w-6" fill="none" stroke="currentColor" strokeWidth="1.8" aria-hidden="true">
        <path d="M4 6h16M4 12h10M4 18h6" />
        <circle cx="16" cy="12" r="2" />
        <circle cx="20" cy="6" r="2" />
        <circle cx="10" cy="18" r="2" />
    </svg>
);

const iconBell = (
    <svg viewBox="0 0 24 24" className="h-6 w-6" fill="none" stroke="currentColor" strokeWidth="1.8" aria-hidden="true">
        <path d="M15 17h5l-1.5-2v-4a6.5 6.5 0 10-13 0v4L4 17h5" />
        <path d="M10 19a2 2 0 004 0" />
    </svg>
);

const colorMap: Record<string, string> = {
    blue:    'bg-blue-50 text-blue-600 border-blue-100',
    indigo:  'bg-indigo-50 text-indigo-600 border-indigo-100',
    emerald: 'bg-emerald-50 text-emerald-600 border-emerald-100',
    amber:   'bg-amber-50 text-amber-600 border-amber-100',
    slate:   'bg-slate-50 text-slate-500 border-slate-100',
};

export default function SettingsIndex({ cards: cardPermissions }: Props) {
    const cards = [
        {
            title: 'Utilizadores',
            description: 'Gerir acessos à plataforma, estado e perfis.',
            icon: iconUsers,
            href: route('admin.settings.users.index'),
            color: 'blue',
            available: cardPermissions.users,
            visible: cardPermissions.users,
            soon: false,
        },
        {
            title: 'Perfis e permissões',
            description: 'Consultar e gerir roles e permissões.',
            icon: iconShield,
            href: route('admin.settings.roles.index'),
            color: 'indigo',
            available: cardPermissions.roles,
            visible: cardPermissions.roles,
            soon: false,
        },
        {
            title: 'Áreas funcionais',
            description: 'Definir responsabilidades para encaminhamento e alertas.',
            icon: iconBuilding,
            href: route('admin.settings.service-areas.index'),
            color: 'emerald',
            available: cardPermissions.serviceAreas,
            visible: cardPermissions.serviceAreas,
            soon: false,
        },
        {
            title: 'Organização',
            description: 'Dados da junta, identidade e parâmetros institucionais.',
            icon: iconBuilding,
            href: route('admin.settings.organization.edit'),
            color: 'amber',
            available: cardPermissions.organization,
            visible: true,
            soon: !cardPermissions.organization,
        },
        {
            title: 'Parâmetros gerais',
            description: 'Categorias, estados, prioridades e opções transversais.',
            icon: iconSliders,
            href: route('admin.settings.index'),
            color: 'slate',
            available: false,
            visible: true,
            soon: true,
        },
        {
            title: 'Notificações',
            description: 'Consultar notificações e acompanhar alertas da plataforma.',
            icon: iconBell,
            href: route('admin.notifications.index'),
            color: 'blue',
            available: cardPermissions.notifications,
            visible: cardPermissions.notifications,
            soon: false,
        },
    ].filter((card) => card.visible);

    return (
        <AdminLayout title="Configurações" subtitle="Gestão estrutural da plataforma">
            <Head title="Configurações" />
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                {cards.map((card) => {
                    const content = (
                        <AppCard className={`flex items-start gap-4 rounded-3xl bg-white transition-shadow ${card.available ? 'cursor-pointer hover:shadow-md' : 'opacity-70'}`}>
                            <div className={`rounded-2xl border p-3 ${colorMap[card.color]}`}>
                                {card.icon}
                            </div>
                            <div className="min-w-0">
                                <p className="font-semibold text-slate-800">{card.title}</p>
                                <p className="mt-0.5 text-sm text-slate-500">{card.description}</p>
                                {card.soon && (
                                    <AppBadge tone="slate" className="mt-2">Em breve</AppBadge>
                                )}
                            </div>
                        </AppCard>
                    );

                    return card.available ? (
                        <Link key={card.title} href={card.href}>{content}</Link>
                    ) : (
                        <div key={card.title}>{content}</div>
                    );
                })}
            </div>
        </AdminLayout>
    );
}
