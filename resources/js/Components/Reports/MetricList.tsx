type MetricListProps = {
    values: Record<string, number | string | null>;
};

export default function MetricList({ values }: MetricListProps) {
    return (
        <dl className="space-y-2 text-sm">
            {Object.entries(values).map(([label, value]) => (
                <div key={label} className="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2">
                    <dt className="text-slate-600">{label}</dt>
                    <dd className="font-semibold text-slate-900">{value ?? '-'}</dd>
                </div>
            ))}
        </dl>
    );
}
