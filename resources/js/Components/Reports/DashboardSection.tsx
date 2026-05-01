import type { ReactNode } from 'react';

type DashboardSectionProps = {
    title: string;
    children: ReactNode;
};

export default function DashboardSection({ title, children }: DashboardSectionProps) {
    return (
        <section className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <h2 className="text-base font-semibold text-slate-900">{title}</h2>
            <div className="mt-3">{children}</div>
        </section>
    );
}
