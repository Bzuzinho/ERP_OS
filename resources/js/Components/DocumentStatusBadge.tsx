type Props = {
    status: string;
};

const styleMap: Record<string, string> = {
    draft: 'bg-amber-100 text-amber-800',
    active: 'bg-emerald-100 text-emerald-800',
    archived: 'bg-slate-200 text-slate-800',
    cancelled: 'bg-rose-100 text-rose-800',
};

export default function DocumentStatusBadge({ status }: Props) {
    return (
        <span className={`inline-flex rounded-full px-2 py-1 text-xs font-semibold ${styleMap[status] ?? 'bg-slate-100 text-slate-700'}`}>
            {status}
        </span>
    );
}
