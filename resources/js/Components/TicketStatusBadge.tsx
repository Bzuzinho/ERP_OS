type TicketStatusBadgeProps = {
    status: string;
};

const statusLabelMap: Record<string, string> = {
    novo: 'Novo',
    em_analise: 'Em analise',
    aguarda_informacao: 'Aguarda informacao',
    encaminhado: 'Encaminhado',
    em_execucao: 'Em execucao',
    agendado: 'Agendado',
    resolvido: 'Resolvido',
    fechado: 'Fechado',
    cancelado: 'Cancelado',
    indeferido: 'Indeferido',
};

const statusClassMap: Record<string, string> = {
    novo: 'bg-slate-100 text-slate-700',
    em_analise: 'bg-blue-100 text-blue-700',
    aguarda_informacao: 'bg-amber-100 text-amber-700',
    encaminhado: 'bg-cyan-100 text-cyan-700',
    em_execucao: 'bg-indigo-100 text-indigo-700',
    agendado: 'bg-purple-100 text-purple-700',
    resolvido: 'bg-emerald-100 text-emerald-700',
    fechado: 'bg-stone-200 text-stone-700',
    cancelado: 'bg-rose-100 text-rose-700',
    indeferido: 'bg-red-100 text-red-700',
};

export default function TicketStatusBadge({ status }: TicketStatusBadgeProps) {
    return (
        <span
            className={`inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ${statusClassMap[status] ?? 'bg-slate-100 text-slate-700'}`}
        >
            {statusLabelMap[status] ?? status}
        </span>
    );
}
