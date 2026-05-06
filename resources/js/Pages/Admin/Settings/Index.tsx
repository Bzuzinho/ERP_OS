import AdminLayout from '@/Layouts/AdminLayout';
import AppCard from '@/Components/App/AppCard';
import { Head, Link } from '@inertiajs/react';

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

const cards = [
    {
        title: 'Utilizadores',
        description: 'Gerir utilizadores, acessos e perfis da plataforma.',
        icon: iconUsers,
        href: route('admin.settings.users.index'),
        color: 'blue',
        available: true,
    },
    {
        title: 'Perfis',
        description: 'Gerir perfis e as permissões atribuídas a cada perfil.',
        icon: iconShield,
        href: route('admin.settings.roles.index'),
        color: 'indigo',
        available: true,
    },
    {
        title: 'Organização',
        description: 'Dados e configurações da organização.',
        icon: iconBuilding,
        href: route('admin.settings.organization.edit'),
        color: 'emerald',
        available: true,
    },
    {
        title: 'Preferências',
        description: 'Preferências gerais da aplicação.',
        icon: iconSliders,
        href: route('admin.settings.index'),
        color: 'slate',
        available: false,
    },
    {
        title: 'Areas funcionais',
        description: 'Definir responsabilidades e associar utilizadores a areas.',
        icon: iconBuilding,
        href: route('admin.service-areas.index'),
        color: 'emerald',
        available: true,
    },
    {
        title: 'Alertas',
        description: 'Consultar alertas e notificacoes internas do sistema.',
        icon: iconBell,
        href: route('admin.notifications.index'),
        color: 'blue',
        available: true,
    },
];

const colorMap: Record<string, string> = {
    blue:    'bg-blue-50 text-blue-600 border-blue-100',
    indigo:  'bg-indigo-50 text-indigo-600 border-indigo-100',
    emerald: 'bg-emerald-50 text-emerald-600 border-emerald-100',
    slate:   'bg-slate-50 text-slate-500 border-slate-100',
};

export default function SettingsIndex() {
    return (
        <AdminLayout title="Configurações" subtitle="Gestão da plataforma">
            <Head title="Configurações" />
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                {cards.map((card) => {
                    const content = (
                        <AppCard className={`flex items-start gap-4 transition-shadow ${card.available ? 'hover:shadow-md cursor-pointer' : 'opacity-50'}`}>
                            <div className={`rounded-2xl border p-3 ${colorMap[card.color]}`}>
                                {card.icon}
                            </div>
                            <div className="min-w-0">
                                <p className="font-semibold text-slate-800">{card.title}</p>
                                <p className="mt-0.5 text-sm text-slate-500">{card.description}</p>
                                {!card.available && (
                                    <span className="mt-1 inline-block rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-400">Em breve</span>
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
