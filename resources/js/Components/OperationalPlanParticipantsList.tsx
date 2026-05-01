type Participant = {
    id: number;
    role?: string | null;
    user?: { id: number; name: string } | null;
    team?: { id: number; name: string } | null;
    employee?: { id: number; employee_number: string } | null;
};

type Props = { participants: Participant[] };

export default function OperationalPlanParticipantsList({ participants }: Props) {
    return (
        <ul className="space-y-2 text-sm">
            {participants.map((participant) => (
                <li key={participant.id} className="rounded-xl bg-slate-50 px-3 py-2 text-slate-700">
                    {participant.user?.name ?? participant.team?.name ?? participant.employee?.employee_number ?? 'Sem identificação'}
                    {participant.role ? ` (${participant.role})` : ''}
                </li>
            ))}
            {participants.length === 0 ? <li className="text-slate-500">Sem participantes definidos.</li> : null}
        </ul>
    );
}
