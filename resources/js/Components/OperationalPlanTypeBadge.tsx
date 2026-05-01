type Props = { type: string };

export default function OperationalPlanTypeBadge({ type }: Props) {
    return <span className="rounded-full bg-slate-200 px-3 py-1 text-xs font-medium text-slate-800">{type}</span>;
}
