type Props = { status: string };

const map: Record<string, string> = {
    ok: 'bg-emerald-100 text-emerald-800',
    low: 'bg-amber-100 text-amber-800',
    out: 'bg-rose-100 text-rose-800',
};

export default function InventoryStockStatusBadge({ status }: Props) {
    return <span className={`inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ${map[status] ?? 'bg-slate-100 text-slate-700'}`}>{status}</span>;
}
