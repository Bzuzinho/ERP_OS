type Props = { status: string };

export default function InventoryLoanStatusBadge({ status }: Props) {
    const color = status === 'overdue' ? 'bg-rose-100 text-rose-800' : status === 'active' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800';

    return <span className={`inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ${color}`}>{status}</span>;
}
