type Participant = {
    id: number;
    role: string | null;
    attendance_status: string;
    user?: { id: number; name: string } | null;
    contact?: { id: number; name: string } | null;
};

type EventParticipantsListProps = {
    participants: Participant[];
};

export default function EventParticipantsList({ participants }: EventParticipantsListProps) {
    return (
        <div className="rounded-2xl border border-slate-200 bg-white p-5">
            <h3 className="text-lg font-semibold text-slate-900">Participantes</h3>
            <ul className="mt-3 space-y-2 text-sm text-slate-700">
                {participants.map((participant) => (
                    <li key={participant.id} className="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <p className="font-medium text-slate-900">{participant.user?.name ?? participant.contact?.name ?? 'Participante'}</p>
                        <p className="text-xs text-slate-600">{participant.role ?? 'Sem papel'} - {participant.attendance_status}</p>
                    </li>
                ))}
                {participants.length === 0 ? <li>Sem participantes.</li> : null}
            </ul>
        </div>
    );
}
