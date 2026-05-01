type StatusBreakdownProps = {
    values: Record<string, number>;
};

export default function StatusBreakdown({ values }: StatusBreakdownProps) {
    return (
        <ul className="grid gap-2 sm:grid-cols-2">
            {Object.entries(values).map(([label, total]) => (
                <li key={label} className="flex items-center justify-between rounded-xl bg-slate-50 px-3 py-2 text-sm">
                    <span className="text-slate-600">{label}</span>
                    <span className="font-semibold text-slate-900">{total}</span>
                </li>
            ))}
        </ul>
    );
}
