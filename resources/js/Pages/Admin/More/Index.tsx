import MobileMoreMenu from '@/Components/App/MobileMoreMenu';
import AdminLayout from '@/Layouts/AdminLayout';
import type { PageProps } from '@/types';
import { usePage } from '@inertiajs/react';

const iconClass = 'h-4 w-4';

const circleIcon = (
    <svg viewBox="0 0 24 24" className={iconClass} fill="none" stroke="currentColor" strokeWidth="1.8" aria-hidden="true">
        <circle cx="12" cy="12" r="7" />
    </svg>
);

const docsIcon = (
    <svg viewBox="0 0 24 24" className={iconClass} fill="none" stroke="currentColor" strokeWidth="1.8" aria-hidden="true">
        <path d="M6 3h9l3 3v15H6z" />
        <path d="M14 3v4h4" />
    </svg>
);

const spaceIcon = (
    <svg viewBox="0 0 24 24" className={iconClass} fill="none" stroke="currentColor" strokeWidth="1.8" aria-hidden="true">
        <path d="M4 20h16" />
        <path d="M6 20V7l6-3 6 3v13" />
    </svg>
);

const userIcon = (
    <svg viewBox="0 0 24 24" className={iconClass} fill="none" stroke="currentColor" strokeWidth="1.8" aria-hidden="true">
        <circle cx="12" cy="9" r="3" />
        <path d="M6 19a6 6 0 0112 0" />
    </svg>
);

const reportIcon = (
    <svg viewBox="0 0 24 24" className={iconClass} fill="none" stroke="currentColor" strokeWidth="1.8" aria-hidden="true">
        <path d="M5 19V5" />
        <path d="M10 19v-8" />
        <path d="M15 19v-4" />
        <path d="M20 19v-11" />
    </svg>
);

const bellIcon = (
    <svg viewBox="0 0 24 24" className={iconClass} fill="none" stroke="currentColor" strokeWidth="1.8" aria-hidden="true">
        <path d="M15 17h5l-1.5-2v-4a6.5 6.5 0 10-13 0v4L4 17h5" />
    </svg>
);

const gearIcon = (
    <svg viewBox="0 0 24 24" className={iconClass} fill="none" stroke="currentColor" strokeWidth="1.8" aria-hidden="true">
        <circle cx="12" cy="12" r="3" />
        <path d="M19.4 15a1.7 1.7 0 00.34 1.87l.06.06a2 2 0 01-2.83 2.83l-.06-.06a1.7 1.7 0 00-1.87-.34 1.7 1.7 0 00-1 1.56V21a2 2 0 01-4 0v-.09a1.7 1.7 0 00-1-1.56 1.7 1.7 0 00-1.87.34l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.7 1.7 0 005 15a1.7 1.7 0 00-1.56-1H3.3a2 2 0 010-4h.09A1.7 1.7 0 005 8.44a1.7 1.7 0 00-.34-1.87l-.06-.06a2 2 0 012.83-2.83l.06.06A1.7 1.7 0 009.44 4a1.7 1.7 0 001-1.56V2.3a2 2 0 014 0v.09a1.7 1.7 0 001 1.56 1.7 1.7 0 001.87-.34l.06-.06a2 2 0 012.83 2.83l-.06.06A1.7 1.7 0 0019 8.44a1.7 1.7 0 001.56 1h.14a2 2 0 010 4h-.09A1.7 1.7 0 0019.4 15z" />
    </svg>
);

export default function AdminMoreIndex() {
    const { auth } = usePage<PageProps>().props;

    return (
        <AdminLayout title="Mais" subtitle="Módulos e conta">
            <MobileMoreMenu
                profile={{
                    name: auth.user?.name ?? 'Utilizador',
                    role: 'Administração',
                    organization: auth.user?.organization?.name ?? 'Junta de Freguesia Demo',
                }}
                sections={[
                    {
                        title: 'Módulos',
                        items: [
                            { label: 'Documentos', href: route('admin.documents.index'), tone: 'blue', icon: docsIcon },
                            { label: 'Espaços', href: route('admin.spaces.index'), tone: 'indigo', icon: spaceIcon },
                            { label: 'Recursos Materiais', href: route('admin.inventory-items.index'), tone: 'amber', icon: circleIcon },
                            { label: 'Recursos Humanos', href: route('admin.hr.employees.index'), tone: 'green', icon: userIcon },
                            { label: 'Planeamento', href: route('admin.operational-plans.index'), tone: 'blue', icon: circleIcon },
                            { label: 'Relatórios', href: route('admin.reports.index'), tone: 'indigo', icon: reportIcon },
                        ],
                    },
                    {
                        title: 'Conta',
                        items: [
                            { label: 'Perfil', href: route('profile.edit'), tone: 'slate', icon: userIcon },
                            { label: 'Notificações', href: route('admin.dashboard'), tone: 'blue', icon: bellIcon },
                            { label: 'Configurações', href: route('admin.settings.index'), tone: 'slate', icon: gearIcon },
                            { label: 'Ajuda e suporte', href: route('admin.dashboard'), tone: 'amber', icon: circleIcon },
                        ],
                    },
                    {
                        title: 'Sessão',
                        items: [{ label: 'Terminar sessão', action: 'logout', tone: 'red', icon: circleIcon }],
                    },
                ]}
            />
        </AdminLayout>
    );
}
