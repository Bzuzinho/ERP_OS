import type { PropsWithChildren } from 'react';

type AppCardProps = PropsWithChildren<{
    className?: string;
}>;

export default function AppCard({ children, className = '' }: AppCardProps) {
    return <section className={`rounded-3xl border border-slate-200/70 bg-white p-5 shadow-sm ${className}`}>{children}</section>;
}
