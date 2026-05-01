type Props = { visibility: string };

const colors: Record<string, string> = {
    public: 'bg-teal-100 text-teal-800',
    portal: 'bg-cyan-100 text-cyan-800',
    internal: 'bg-slate-100 text-slate-800',
    restricted: 'bg-rose-100 text-rose-800',
};

export default function OperationalPlanVisibilityBadge({ visibility }: Props) {
    return <span className={`rounded-full px-3 py-1 text-xs font-semibold ${colors[visibility] ?? 'bg-slate-100 text-slate-700'}`}>{visibility}</span>;
}
