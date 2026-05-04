import AppCard from '@/Components/App/AppCard';

type KpiCardProps = {
    label: string;
    value: string | number;
    hint?: string;
};

export default function KpiCard({ label, value, hint }: KpiCardProps) {
    return (
        <AppCard className="p-4">
            <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">{label}</p>
            <p className="mt-2 text-2xl font-bold text-slate-900">{value}</p>
            {hint ? <p className="mt-1 text-xs text-slate-500">{hint}</p> : null}
        </AppCard>
    );
}
