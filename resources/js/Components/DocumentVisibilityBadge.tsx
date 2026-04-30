type Props = {
    visibility: string;
};

const styleMap: Record<string, string> = {
    public: 'bg-emerald-100 text-emerald-800',
    portal: 'bg-amber-100 text-amber-800',
    internal: 'bg-slate-100 text-slate-800',
    restricted: 'bg-rose-100 text-rose-800',
};

export default function DocumentVisibilityBadge({ visibility }: Props) {
    return (
        <span className={`inline-flex rounded-full px-2 py-1 text-xs font-semibold ${styleMap[visibility] ?? 'bg-slate-100 text-slate-700'}`}>
            {visibility}
        </span>
    );
}
