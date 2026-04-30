import { router } from '@inertiajs/react';
import { FormEvent, useState } from 'react';

type EventOption = { id: number; title: string };

type Props = {
    statuses: string[];
    events: EventOption[];
    initialFilters: {
        search?: string;
        status?: string;
        eventId?: string;
        date?: string;
    };
};

export default function MeetingMinuteFilters({ statuses, events, initialFilters }: Props) {
    const [search, setSearch] = useState(initialFilters.search ?? '');
    const [status, setStatus] = useState(initialFilters.status ?? '');
    const [eventId, setEventId] = useState(initialFilters.eventId ?? '');
    const [date, setDate] = useState(initialFilters.date ?? '');

    const submit = (event: FormEvent) => {
        event.preventDefault();

        router.get(route('admin.meeting-minutes.index'), {
            search: search || undefined,
            status: status || undefined,
            event_id: eventId || undefined,
            date: date || undefined,
        }, { preserveState: true, replace: true });
    };

    return (
        <form onSubmit={submit} className="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 md:grid-cols-5">
            <input value={search} onChange={(event) => setSearch(event.target.value)} placeholder="Pesquisar ata" className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
            <select value={status} onChange={(event) => setStatus(event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                <option value="">Estado</option>
                {statuses.map((item) => <option key={item} value={item}>{item}</option>)}
            </select>
            <select value={eventId} onChange={(event) => setEventId(event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                <option value="">Evento</option>
                {events.map((item) => <option key={item.id} value={item.id}>{item.title}</option>)}
            </select>
            <input type="date" value={date} onChange={(event) => setDate(event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
            <button type="submit" className="rounded-xl bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700">Filtrar</button>
        </form>
    );
}
