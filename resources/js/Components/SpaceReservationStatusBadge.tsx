type Props = { status: string };

const classes: Record<string, string> = {
    requested: 'bg-amber-100 text-amber-800',
    approved: 'bg-emerald-100 text-emerald-800',
    rejected: 'bg-rose-100 text-rose-800',
    cancelled: 'bg-slate-100 text-slate-700',
    completed: 'bg-blue-100 text-blue-800',
};

export default function SpaceReservationStatusBadge({ status }: Props) {
    return <span className={`inline-flex rounded-full px-2 py-1 text-xs font-semibold ${classes[status] ?? classes.requested}`}>{status}</span>;
}
