type EventStatusBadgeProps = { status: string };

const labelMap: Record<string, string> = {
    scheduled: 'Agendado',
    confirmed: 'Confirmado',
    cancelled: 'Cancelado',
    completed: 'Concluido',
};

const classMap: Record<string, string> = {
    scheduled: 'bg-slate-100 text-slate-700',
    confirmed: 'bg-blue-100 text-blue-700',
    cancelled: 'bg-rose-100 text-rose-700',
    completed: 'bg-emerald-100 text-emerald-700',
};

export default function EventStatusBadge({ status }: EventStatusBadgeProps) {
    return (
        <span className={`inline-flex rounded-full px-3 py-1 text-xs font-semibold ${classMap[status] ?? 'bg-slate-100 text-slate-700'}`}>
            {labelMap[status] ?? status}
        </span>
    );
}
