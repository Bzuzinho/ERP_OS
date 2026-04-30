type Props = {
    status: string;
};

const styleMap: Record<string, string> = {
    draft: 'bg-amber-100 text-amber-800',
    reviewed: 'bg-blue-100 text-blue-800',
    approved: 'bg-emerald-100 text-emerald-800',
    archived: 'bg-slate-200 text-slate-800',
};

export default function MeetingMinuteStatusBadge({ status }: Props) {
    return (
        <span className={`inline-flex rounded-full px-2 py-1 text-xs font-semibold ${styleMap[status] ?? 'bg-slate-100 text-slate-700'}`}>
            {status}
        </span>
    );
}
