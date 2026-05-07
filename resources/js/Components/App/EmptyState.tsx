import AppCard from '@/Components/App/AppCard';
import { Link } from '@inertiajs/react';
import type { ReactNode } from 'react';

type EmptyStateProps = {
    title: string;
    description?: string;
    actionLabel?: string;
    actionHref?: string;
    action?: ReactNode;
};

export default function EmptyState({ title, description, actionLabel, actionHref, action }: EmptyStateProps) {
    return (
        <AppCard className="py-8 text-center">
            <div className="mx-auto mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                <svg viewBox="0 0 24 24" className="h-5 w-5" fill="none" stroke="currentColor" strokeWidth="1.8" aria-hidden="true">
                    <path d="M4 7h16" />
                    <path d="M6 12h12" />
                    <path d="M8 17h8" />
                </svg>
            </div>
            <h3 className="text-base font-semibold text-slate-900">{title}</h3>
            {description ? <p className="mt-1 text-sm text-slate-500">{description}</p> : null}
            {action ? <div className="mt-4">{action}</div> : null}
            {!action && actionLabel && actionHref ? (
                <div className="mt-4">
                    <Link
                        href={actionHref}
                        className="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700"
                    >
                        {actionLabel}
                    </Link>
                </div>
            ) : null}
        </AppCard>
    );
}
