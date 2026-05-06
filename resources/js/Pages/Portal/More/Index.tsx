import MobileMoreMenu from '@/Components/App/MobileMoreMenu';
import PortalLayout from '@/Layouts/PortalLayout';
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

const bellIcon = (
    <svg viewBox="0 0 24 24" className={iconClass} fill="none" stroke="currentColor" strokeWidth="1.8" aria-hidden="true">
        <path d="M15 17h5l-1.5-2v-4a6.5 6.5 0 10-13 0v4L4 17h5" />
    </svg>
);

export default function PortalMoreIndex() {
    const { auth } = usePage<PageProps>().props;

    return (
        <PortalLayout title="Mais" subtitle="Conta e serviços">
            <MobileMoreMenu
                profile={{
                    name: auth.user?.name ?? 'Utilizador',
                    role: 'Portal',
                    organization: auth.user?.organization?.name ?? 'Junta de Freguesia Demo',
                    avatarUrl: auth.user?.avatar_path ? `/storage/${auth.user.avatar_path}` : null,
                    organizationLogoUrl: auth.user?.organization?.logo_path ? `/storage/${auth.user.organization.logo_path}` : null,
                }}
                sections={[
                    {
                        title: 'Módulos',
                        items: [
                            { label: 'Documentos', href: route('portal.documents.index'), tone: 'blue', icon: docsIcon },
                            { label: 'Reservas', href: route('portal.space-reservations.index'), tone: 'indigo', icon: spaceIcon },
                            { label: 'Agenda', href: route('portal.events.index'), tone: 'green', icon: circleIcon },
                        ],
                    },
                    {
                        title: 'Conta',
                        items: [
                            { label: 'Perfil', href: route('profile.edit'), tone: 'slate', icon: userIcon },
                            { label: 'Notificações', href: route('portal.notifications.index'), tone: 'blue', icon: bellIcon },
                            { label: 'Ajuda e suporte', href: route('portal.dashboard'), tone: 'amber', icon: circleIcon },
                        ],
                    },
                    {
                        title: 'Sessão',
                        items: [{ label: 'Terminar sessão', action: 'logout', tone: 'red', icon: circleIcon }],
                    },
                ]}
            />
        </PortalLayout>
    );
}
