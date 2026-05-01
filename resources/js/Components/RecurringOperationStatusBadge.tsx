type Props = { status: string };

const colors: Record<string, string> = {
    active: 'bg-emerald-100 text-emerald-800',
    paused: 'bg-amber-100 text-amber-800',
    completed: 'bg-blue-100 text-blue-800',
    cancelled: 'bg-rose-100 text-rose-800',
};

export default function RecurringOperationStatusBadge({ status }: Props) {
    return <span className={`rounded-full px-3 py-1 text-xs font-semibold ${colors[status] ?? 'bg-slate-100 text-slate-700'}`}>{status}</span>;
}
