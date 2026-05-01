type StatusBadgeProps = {
    status: string;
    map?: Record<string, { label: string; className: string }>;
};

const defaultMap: Record<string, { label: string; className: string }> = {
    active: { label: 'Ativo', className: 'bg-emerald-100 text-emerald-800' },
    inactive: { label: 'Inativo', className: 'bg-slate-100 text-slate-600' },
    open: { label: 'Aberto', className: 'bg-blue-100 text-blue-800' },
    closed: { label: 'Fechado', className: 'bg-slate-100 text-slate-600' },
    pending: { label: 'Pendente', className: 'bg-amber-100 text-amber-800' },
    approved: { label: 'Aprovado', className: 'bg-emerald-100 text-emerald-800' },
    rejected: { label: 'Rejeitado', className: 'bg-rose-100 text-rose-800' },
    cancelled: { label: 'Cancelado', className: 'bg-slate-100 text-slate-500' },
    completed: { label: 'Concluído', className: 'bg-emerald-100 text-emerald-800' },
    draft: { label: 'Rascunho', className: 'bg-slate-100 text-slate-600' },
    in_progress: { label: 'Em curso', className: 'bg-blue-100 text-blue-800' },
    scheduled: { label: 'Agendado', className: 'bg-violet-100 text-violet-800' },
};

export default function StatusBadge({ status, map }: StatusBadgeProps) {
    const resolved = { ...defaultMap, ...(map ?? {}) };
    const entry = resolved[status];

    if (!entry) {
        return (
            <span className="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">
                {status}
            </span>
        );
    }

    return (
        <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${entry.className}`}>
            {entry.label}
        </span>
    );
}
