type Props = { status: string };

const map: Record<string, string> = {
    active: 'bg-emerald-100 text-emerald-800',
    inactive: 'bg-slate-100 text-slate-700',
    damaged: 'bg-amber-100 text-amber-800',
    lost: 'bg-rose-100 text-rose-800',
    maintenance: 'bg-blue-100 text-blue-800',
    retired: 'bg-slate-200 text-slate-800',
};

export default function InventoryItemStatusBadge({ status }: Props) {
    return <span className={`inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ${map[status] ?? 'bg-slate-100 text-slate-700'}`}>{status}</span>;
}
