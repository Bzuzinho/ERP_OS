import NotificationBadge from '@/Components/App/NotificationBadge';
import NotificationDropdown from '@/Components/App/NotificationDropdown';
import type { PageProps } from '@/types';
import { usePage } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';

type NotificationBellProps = {
    compact?: boolean;
};

export default function NotificationBell({ compact = false }: NotificationBellProps) {
    const page = usePage<PageProps>();
    const { notifications } = page.props;
    const [open, setOpen] = useState(false);
    const containerRef = useRef<HTMLDivElement | null>(null);

    const routePrefix = route().current('portal.*') ? 'portal' : 'admin';

    useEffect(() => {
        if (!open) {
            return;
        }

        const onClickOutside = (event: MouseEvent) => {
            if (!containerRef.current?.contains(event.target as Node)) {
                setOpen(false);
            }
        };

        document.addEventListener('mousedown', onClickOutside);
        return () => document.removeEventListener('mousedown', onClickOutside);
    }, [open]);

    return (
        <div ref={containerRef} className="relative">
            <button
                type="button"
                onClick={() => setOpen((state) => !state)}
                className={`relative inline-flex items-center justify-center rounded-xl border border-slate-200 text-slate-600 transition hover:bg-slate-50 ${compact ? 'h-9 w-9' : 'h-10 w-10'}`}
            >
                <svg viewBox="0 0 24 24" className="h-5 w-5" fill="none" stroke="currentColor" strokeWidth="1.8" aria-hidden="true">
                    <path d="M15 17h5l-1.5-2v-4a6.5 6.5 0 10-13 0v4L4 17h5" />
                    <path d="M10 19a2 2 0 004 0" />
                </svg>
                <NotificationBadge count={notifications.unread_count} />
            </button>

            {open ? (
                <NotificationDropdown
                    recent={notifications.recent}
                    markAllRoute={route(`${routePrefix}.notifications.mark-all-read`)}
                    indexRoute={route(`${routePrefix}.notifications.index`)}
                    markReadRouteForRecipient={(recipientId) => route(`${routePrefix}.notifications.mark-read`, recipientId)}
                />
            ) : null}
        </div>
    );
}
