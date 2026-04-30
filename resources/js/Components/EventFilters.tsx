import { router } from '@inertiajs/react';
import { FormEvent, useState } from 'react';

type EventFiltersProps = {
    statuses: string[];
    eventTypes: string[];
    initialFilters: { search?: string; status?: string; eventType?: string; date?: string };
};

export default function EventFilters({ statuses, eventTypes, initialFilters }: EventFiltersProps) {
    const [search, setSearch] = useState(initialFilters.search ?? '');
    const [status, setStatus] = useState(initialFilters.status ?? '');
    const [eventType, setEventType] = useState(initialFilters.eventType ?? '');
    const [date, setDate] = useState(initialFilters.date ?? '');

    const submit = (event: FormEvent) => {
        event.preventDefault();

        router.get(route('admin.events.index'), {
            search: search || undefined,
            status: status || undefined,
            event_type: eventType || undefined,
            date: date || undefined,
        }, { preserveState: true, replace: true });
    };

    return (
        <form onSubmit={submit} className="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 md:grid-cols-5">
            <input value={search} onChange={(event) => setSearch(event.target.value)} placeholder="Pesquisar evento" className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
            <select value={eventType} onChange={(event) => setEventType(event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                <option value="">Tipo</option>
                {eventTypes.map((item) => <option key={item} value={item}>{item}</option>)}
            </select>
            <select value={status} onChange={(event) => setStatus(event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                <option value="">Estado</option>
                {statuses.map((item) => <option key={item} value={item}>{item}</option>)}
            </select>
            <input type="date" value={date} onChange={(event) => setDate(event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
            <button type="submit" className="rounded-xl bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700">Filtrar</button>
        </form>
    );
}
