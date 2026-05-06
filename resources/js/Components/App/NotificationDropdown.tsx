import EmptyState from '@/Components/App/EmptyState';
import NotificationItem from '@/Components/App/NotificationItem';
import type { NotificationPreview } from '@/types';
import { Link, router } from '@inertiajs/react';

type NotificationDropdownProps = {
    recent: NotificationPreview[];
    markAllRoute: string;
    indexRoute: string;
    markReadRouteForRecipient: (recipientId: number) => string;
};

export default function NotificationDropdown({
    recent,
    markAllRoute,
    indexRoute,
    markReadRouteForRecipient,
}: NotificationDropdownProps) {
    return (
        <div className="absolute right-0 top-12 z-50 w-[360px] max-w-[90vw] rounded-2xl border border-slate-200 bg-white p-3 shadow-xl">
            <div className="mb-2 flex items-center justify-between">
                <h3 className="text-sm font-semibold text-slate-900">Alertas</h3>
                <button
                    type="button"
                    onClick={() => router.post(markAllRoute, {}, { preserveScroll: true })}
                    className="text-xs font-semibold text-blue-700 hover:text-blue-900"
                >
                    Marcar todos como lidos
                </button>
            </div>

            <div className="max-h-96 space-y-2 overflow-y-auto pr-1">
                {recent.length ? recent.map((item) => (
                    <NotificationItem
                        key={item.recipient_id}
                        item={item}
                        markReadRoute={markReadRouteForRecipient(item.recipient_id)}
                    />
                )) : (
                    <EmptyState
                        title="Sem alertas recentes"
                        description="Quando surgirem novos alertas, vao aparecer aqui."
                    />
                )}
            </div>

            <div className="mt-3 border-t border-slate-200 pt-2 text-right">
                <Link href={indexRoute} className="text-xs font-semibold text-slate-700 hover:text-slate-900">
                    Ver todos
                </Link>
            </div>
        </div>
    );
}
