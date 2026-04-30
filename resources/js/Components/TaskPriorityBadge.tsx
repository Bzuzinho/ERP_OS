type TaskPriorityBadgeProps = { priority: string };

const labelMap: Record<string, string> = {
    low: 'Baixa',
    normal: 'Normal',
    high: 'Alta',
    urgent: 'Urgente',
};

const classMap: Record<string, string> = {
    low: 'bg-emerald-100 text-emerald-700',
    normal: 'bg-slate-100 text-slate-700',
    high: 'bg-amber-100 text-amber-700',
    urgent: 'bg-rose-100 text-rose-700',
};

export default function TaskPriorityBadge({ priority }: TaskPriorityBadgeProps) {
    return (
        <span className={`inline-flex rounded-full px-3 py-1 text-xs font-semibold ${classMap[priority] ?? 'bg-slate-100 text-slate-700'}`}>
            {labelMap[priority] ?? priority}
        </span>
    );
}
