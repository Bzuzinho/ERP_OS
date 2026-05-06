import NotificationItem from '@/Components/App/NotificationItem';
import AdminLayout from '@/Layouts/AdminLayout';
import type { NotificationPreview } from '@/types';
import { Link, router } from '@inertiajs/react';

type RecipientRow = {
    id: number;
    read_at: string | null;
    created_at: string;
    notification: {
        id: number;
        type: string;
        title: string;
        message: string | null;
        action_url: string | null;
        priority: string;
    };
};

type Props = {
    notifications: {
        data: RecipientRow[];
    };
    filter: string;
};

const filters = [
    { key: 'all', label: 'Todos' },
    { key: 'unread', label: 'Nao lidos' },
    { key: 'read', label: 'Lidos' },
    { key: 'archived', label: 'Arquivados' },
];

export default function AdminNotificationsIndex({ notifications, filter }: Props) {
    return (
        <AdminLayout title="Notificações" subtitle="Notificações internas da plataforma">
            <div className="mb-4 flex flex-wrap items-center gap-2">
                {filters.map((item) => (
                    <Link
                        key={item.key}
                        href={route('admin.notifications.index', { filter: item.key })}
                        className={`rounded-xl border px-3 py-1.5 text-sm ${filter === item.key ? 'border-blue-300 bg-blue-50 text-blue-700' : 'border-slate-200 text-slate-700 hover:bg-slate-50'}`}
                    >
                        {item.label}
                    </Link>
                ))}

                <button
                    type="button"
                    onClick={() => router.post(route('admin.notifications.mark-all-read'))}
                    className="ml-auto rounded-xl border border-slate-200 px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                >
                    Marcar todos como lidos
                </button>
            </div>

            <div className="space-y-2 rounded-2xl border border-slate-200 bg-white p-3">
                {notifications.data.length ? notifications.data.map((recipient) => {
                    const item: NotificationPreview = {
                        recipient_id: recipient.id,
                        id: recipient.id,
                        notification_id: recipient.notification.id,
                        title: recipient.notification.title,
                        message: recipient.notification.message,
                        priority: recipient.notification.priority,
                        type: recipient.notification.type,
                        action_url: recipient.notification.action_url,
                        read_at: recipient.read_at,
                        created_at: recipient.created_at,
                        created_at_human: null,
                    };

                    return (
                        <NotificationItem
                            key={recipient.id}
                            item={item}
                            markReadRoute={route('admin.notifications.mark-read', recipient.id)}
                        />
                    );
                }) : <p className="px-2 py-4 text-sm text-slate-500">Nao existem notificações para o filtro selecionado.</p>}
            </div>
        </AdminLayout>
    );
}
