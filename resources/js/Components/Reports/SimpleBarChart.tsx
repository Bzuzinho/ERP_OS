type BarDatum = {
    label: string;
    value: number;
};

type SimpleBarChartProps = {
    title: string;
    data: BarDatum[];
};

export default function SimpleBarChart({ title, data }: SimpleBarChartProps) {
    const max = Math.max(...data.map((item) => item.value), 1);

    return (
        <section className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <h3 className="text-sm font-semibold text-slate-900">{title}</h3>
            <div className="mt-4 space-y-2">
                {data.map((item) => (
                    <div key={item.label}>
                        <div className="mb-1 flex items-center justify-between text-xs text-slate-600">
                            <span>{item.label}</span>
                            <span>{item.value}</span>
                        </div>
                        <div className="h-2 rounded-full bg-slate-100">
                            <div
                                className="h-2 rounded-full bg-emerald-600"
                                style={{ width: `${Math.max((item.value / max) * 100, 4)}%` }}
                            />
                        </div>
                    </div>
                ))}
            </div>
        </section>
    );
}
