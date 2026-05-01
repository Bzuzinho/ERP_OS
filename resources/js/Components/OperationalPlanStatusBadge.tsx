type Props = { status: string };

const labels: Record<string, string> = {
    draft: 'Rascunho',
    pending_approval: 'Pendente',
    approved: 'Aprovado',
    scheduled: 'Agendado',
    in_progress: 'Em execução',
    completed: 'Concluído',
    cancelled: 'Cancelado',
    archived: 'Arquivado',
};

export default function OperationalPlanStatusBadge({ status }: Props) {
    const color = {
        draft: 'bg-slate-100 text-slate-700',
        pending_approval: 'bg-amber-100 text-amber-800',
        approved: 'bg-emerald-100 text-emerald-800',
        scheduled: 'bg-sky-100 text-sky-800',
        in_progress: 'bg-indigo-100 text-indigo-800',
        completed: 'bg-green-100 text-green-800',
        cancelled: 'bg-rose-100 text-rose-800',
        archived: 'bg-zinc-100 text-zinc-700',
    }[status] ?? 'bg-slate-100 text-slate-700';

    return <span className={`rounded-full px-3 py-1 text-xs font-semibold ${color}`}>{labels[status] ?? status}</span>;
}
