import type { ReactNode } from 'react';

type KpiGridProps = {
    children: ReactNode;
};

export default function KpiGrid({ children }: KpiGridProps) {
    return <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">{children}</div>;
}
