type Props = { status: string };

const classes: Record<string, string> = {
    available: 'bg-emerald-100 text-emerald-800',
    unavailable: 'bg-amber-100 text-amber-800',
    maintenance: 'bg-orange-100 text-orange-800',
    inactive: 'bg-slate-100 text-slate-700',
};

export default function SpaceStatusBadge({ status }: Props) {
    return <span className={`inline-flex rounded-full px-2 py-1 text-xs font-semibold ${classes[status] ?? classes.inactive}`}>{status}</span>;
}
