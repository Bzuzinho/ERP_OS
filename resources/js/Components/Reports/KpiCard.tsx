type KpiCardProps = {
    label: string;
    value: string | number;
    hint?: string;
};

export default function KpiCard({ label, value, hint }: KpiCardProps) {
    return (
        <article className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <p className="text-xs uppercase tracking-[0.18em] text-slate-500">{label}</p>
            <p className="mt-2 text-3xl font-semibold text-slate-900">{value}</p>
            {hint ? <p className="mt-1 text-xs text-slate-500">{hint}</p> : null}
        </article>
    );
}
