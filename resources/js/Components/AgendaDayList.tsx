import EventStatusBadge from '@/Components/EventStatusBadge';

type AgendaEvent = {
    id: number;
    title: string;
    start_at: string;
    end_at: string;
    status: string;
    location_text?: string | null;
};

type AgendaDayListProps = {
    events: AgendaEvent[];
};

export default function AgendaDayList({ events }: AgendaDayListProps) {
    return (
        <div className="rounded-2xl border border-slate-200 bg-white p-5">
            <h3 className="text-lg font-semibold text-slate-900">Agenda de Hoje</h3>
            <ul className="mt-3 space-y-2">
                {events.map((event) => (
                    <li key={event.id} className="rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm">
                        <div className="flex items-center justify-between gap-2">
                            <p className="font-medium text-slate-900">{event.title}</p>
                            <EventStatusBadge status={event.status} />
                        </div>
                        <p className="mt-1 text-xs text-slate-600">
                            {new Date(event.start_at).toLocaleTimeString()} - {new Date(event.end_at).toLocaleTimeString()} {event.location_text ? `| ${event.location_text}` : ''}
                        </p>
                    </li>
                ))}
                {events.length === 0 ? <li className="text-sm text-slate-500">Sem eventos para hoje.</li> : null}
            </ul>
        </div>
    );
}
