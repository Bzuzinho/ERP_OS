type TicketPriorityBadgeProps = {
    priority: string;
};

const priorityLabelMap: Record<string, string> = {
    low: 'Baixa',
    normal: 'Normal',
    high: 'Alta',
    urgent: 'Urgente',
};

const priorityClassMap: Record<string, string> = {
    low: 'bg-emerald-100 text-emerald-700',
    normal: 'bg-slate-100 text-slate-700',
    high: 'bg-amber-100 text-amber-700',
    urgent: 'bg-rose-100 text-rose-700',
};

export default function TicketPriorityBadge({ priority }: TicketPriorityBadgeProps) {
    return (
        <span
            className={`inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ${priorityClassMap[priority] ?? 'bg-slate-100 text-slate-700'}`}
        >
            {priorityLabelMap[priority] ?? priority}
        </span>
    );
}
