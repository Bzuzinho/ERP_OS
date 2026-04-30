type ApprovalItem = {
    id: number;
    action: string;
    old_status: string | null;
    new_status: string;
    notes: string | null;
    created_at: string;
    decided_by?: { id: number; name: string } | null;
};

type Props = {
    approvals: ApprovalItem[];
};

export default function SpaceReservationTimeline({ approvals }: Props) {
    return (
        <ul className="space-y-2">
            {approvals.map((item) => (
                <li key={item.id} className="rounded-xl border border-slate-200 bg-white p-3 text-sm text-slate-700">
                    <p className="font-medium text-slate-900">{item.action} - {item.new_status}</p>
                    <p>Por: {item.decided_by?.name ?? 'Sistema'}</p>
                    <p>{new Date(item.created_at).toLocaleString()}</p>
                    {item.notes ? <p className="mt-1 text-slate-600">{item.notes}</p> : null}
                </li>
            ))}
            {approvals.length === 0 ? <li className="text-sm text-slate-500">Sem historico.</li> : null}
        </ul>
    );
}
