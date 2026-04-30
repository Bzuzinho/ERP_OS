import AgendaDayList from '@/Components/AgendaDayList';
import EventFilters from '@/Components/EventFilters';
import EventStatusBadge from '@/Components/EventStatusBadge';
import EventTypeBadge from '@/Components/EventTypeBadge';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link } from '@inertiajs/react';

type EventRow = {
    id: number;
    title: string;
    start_at: string;
    end_at: string;
    status: string;
    event_type: string;
    location_text?: string | null;
};

type Props = {
    events: { data: EventRow[] };
    dayList: EventRow[];
    filters: { search?: string; status?: string; eventType?: string; date?: string };
    statuses: string[];
    eventTypes: string[];
};

export default function Index({ events, dayList, filters, statuses, eventTypes }: Props) {
    return (
        <AdminLayout title="Agenda" subtitle="Planeamento de eventos internos, publicos e restritos.">
            <Head title="Agenda" />
            <div className="space-y-6">
                <div className="flex items-center justify-between gap-4">
                    <h1 className="text-2xl font-semibold text-slate-900">Agenda</h1>
                    <Link href={route('admin.events.create')} className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                        Novo evento
                    </Link>
                </div>

                <EventFilters statuses={statuses} eventTypes={eventTypes} initialFilters={filters} />

                <div className="grid gap-3">
                    {events.data.map((event) => (
                        <article key={event.id} className="rounded-2xl border border-slate-200 bg-white p-4">
                            <div className="flex items-center justify-between gap-3">
                                <Link href={route('admin.events.show', event.id)} className="font-semibold text-slate-900 hover:text-slate-700">{event.title}</Link>
                                <EventStatusBadge status={event.status} />
                            </div>
                            <div className="mt-2 flex flex-wrap items-center gap-2 text-xs">
                                <EventTypeBadge eventType={event.event_type} />
                                <span className="text-slate-600">{new Date(event.start_at).toLocaleString()} - {new Date(event.end_at).toLocaleString()}</span>
                                {event.location_text ? <span className="text-slate-500">{event.location_text}</span> : null}
                            </div>
                        </article>
                    ))}
                    {events.data.length === 0 ? <p className="text-sm text-slate-500">Sem eventos encontrados.</p> : null}
                </div>

                <AgendaDayList events={dayList} />
            </div>
        </AdminLayout>
    );
}
