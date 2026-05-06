type ApprovalItem = {
    id: number;
    action: string;
    new_status: string;
    notes: string | null;
    created_at: string;
};

type Props = {
    approvals: ApprovalItem[];
};

const statusLabel: Record<string, string> = {
    requested: 'Pedido recebido',
    approved: 'Aprovado',
    rejected: 'Rejeitado',
    cancelled: 'Cancelado',
    completed: 'Concluido',
};

export default function PublicReservationTimeline({ approvals }: Props) {
    return (
        <ul className="space-y-2">
            {approvals.map((item) => (
                <li key={item.id} className="rounded-xl border border-slate-200 bg-white p-3 text-sm text-slate-700">
                    <p className="font-medium text-slate-900">{statusLabel[item.new_status] ?? 'Em analise'}</p>
                    <p>{new Date(item.created_at).toLocaleString()}</p>
                    {item.notes ? <p className="mt-1 text-slate-600">{item.notes}</p> : null}
                </li>
            ))}
            {approvals.length === 0 ? <li className="text-sm text-slate-500">Sem atualizacoes.</li> : null}
        </ul>
    );
}
