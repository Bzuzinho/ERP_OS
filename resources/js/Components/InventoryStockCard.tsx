type Props = {
    title: string;
    value: string | number;
    subtitle?: string;
};

export default function InventoryStockCard({ title, value, subtitle }: Props) {
    return (
        <section className="rounded-2xl border border-slate-200 bg-white p-4">
            <p className="text-sm text-slate-500">{title}</p>
            <p className="mt-2 text-2xl font-semibold text-slate-950">{value}</p>
            {subtitle ? <p className="mt-1 text-xs text-slate-500">{subtitle}</p> : null}
        </section>
    );
}
