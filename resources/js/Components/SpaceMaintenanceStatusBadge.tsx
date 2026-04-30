type Props = { status: string };

const classes: Record<string, string> = {
    pending: 'bg-amber-100 text-amber-800',
    scheduled: 'bg-blue-100 text-blue-800',
    in_progress: 'bg-indigo-100 text-indigo-800',
    completed: 'bg-emerald-100 text-emerald-800',
    cancelled: 'bg-slate-100 text-slate-700',
};

export default function SpaceMaintenanceStatusBadge({ status }: Props) {
    return <span className={`inline-flex rounded-full px-2 py-1 text-xs font-semibold ${classes[status] ?? classes.pending}`}>{status}</span>;
}
