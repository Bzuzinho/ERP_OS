type TaskStatusBadgeProps = { status: string };

const labelMap: Record<string, string> = {
    pending: 'Pendente',
    in_progress: 'Em curso',
    waiting: 'Em espera',
    done: 'Concluida',
    cancelled: 'Cancelada',
};

const classMap: Record<string, string> = {
    pending: 'bg-slate-100 text-slate-700',
    in_progress: 'bg-blue-100 text-blue-700',
    waiting: 'bg-amber-100 text-amber-700',
    done: 'bg-emerald-100 text-emerald-700',
    cancelled: 'bg-rose-100 text-rose-700',
};

export default function TaskStatusBadge({ status }: TaskStatusBadgeProps) {
    return (
        <span className={`inline-flex rounded-full px-3 py-1 text-xs font-semibold ${classMap[status] ?? 'bg-slate-100 text-slate-700'}`}>
            {labelMap[status] ?? status}
        </span>
    );
}
