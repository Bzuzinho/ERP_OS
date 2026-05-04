import type { PropsWithChildren } from 'react';

type AppBadgeTone = 'blue' | 'amber' | 'green' | 'red' | 'slate' | 'indigo';

type AppBadgeProps = PropsWithChildren<{
    tone?: AppBadgeTone;
    className?: string;
}>;

const toneClassMap: Record<AppBadgeTone, string> = {
    blue: 'bg-blue-50 text-blue-700 ring-blue-100',
    amber: 'bg-amber-50 text-amber-700 ring-amber-100',
    green: 'bg-emerald-50 text-emerald-700 ring-emerald-100',
    red: 'bg-rose-50 text-rose-700 ring-rose-100',
    slate: 'bg-slate-100 text-slate-700 ring-slate-200',
    indigo: 'bg-indigo-50 text-indigo-700 ring-indigo-100',
};

export default function AppBadge({ tone = 'slate', className = '', children }: AppBadgeProps) {
    return (
        <span
            className={`inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset ${toneClassMap[tone]} ${className}`}
        >
            {children}
        </span>
    );
}
