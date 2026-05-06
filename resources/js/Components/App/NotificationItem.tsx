import type { NotificationPreview } from '@/types';
import { router } from '@inertiajs/react';

type NotificationItemProps = {
    item: NotificationPreview;
    markReadRoute: string;
};

const priorityClass: Record<string, string> = {
    low: 'bg-slate-100 text-slate-700',
    normal: 'bg-blue-100 text-blue-700',
    high: 'bg-amber-100 text-amber-700',
    urgent: 'bg-rose-100 text-rose-700',
};

function resolvePortalActionUrl(actionUrl: string | null): string | null {
    if (!actionUrl) {
        return null;
    }

    if (actionUrl.startsWith('/admin/tickets/')) {
        return actionUrl.replace('/admin/tickets/', '/portal/tickets/');
    }

    return actionUrl;
}

export default function NotificationItem({ item, markReadRoute }: NotificationItemProps) {
    const handleClick = () => {
        const targetUrl = resolvePortalActionUrl(item.action_url);

        const goToTarget = () => {
            if (targetUrl) {
                router.visit(targetUrl);
            }
        };

        if (item.read_at) {
            goToTarget();
            return;
        }

        router.post(markReadRoute, {}, {
            preserveScroll: true,
            onSuccess: goToTarget,
        });
    };

    return (
        <button
            type="button"
            onClick={handleClick}
            className={`w-full rounded-2xl border px-3 py-2.5 text-left transition hover:bg-slate-50 ${item.read_at ? 'border-slate-200 bg-white' : 'border-blue-200 bg-blue-50/60'}`}
        >
            <div className="flex items-start justify-between gap-3">
                <p className="text-sm font-semibold text-slate-900">{item.title ?? 'Alerta'}</p>
                <span className={`shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase ${priorityClass[item.priority] ?? priorityClass.normal}`}>
                    {item.priority}
                </span>
            </div>
            {item.message ? <p className="mt-1 line-clamp-2 text-xs text-slate-600">{item.message}</p> : null}
            <div className="mt-2 flex items-center justify-between text-[11px] text-slate-500">
                <span>{item.created_at_human ?? 'agora'}</span>
                <span>{item.read_at ? 'Lido' : 'Nao lido'}</span>
            </div>
            <div className="mt-2 text-right text-xs font-semibold text-blue-700">Abrir</div>
        </button>
    );
}
