import EventStatusBadge from '@/Components/EventStatusBadge';
import EventTypeBadge from '@/Components/EventTypeBadge';
import PortalLayout from '@/Layouts/PortalLayout';
import { Head, Link } from '@inertiajs/react';

type EventRow = {
    id: number;
    title: string;
    start_at: string;
    end_at: string;
    status: string;
    event_type: string;
    related_contact?: { id: number; name: string } | null;
    related_ticket?: { id: number; reference: string; title: string } | null;
};

type Props = {
    events: { data: EventRow[] };
};

export default function Index({ events }: Props) {
    return (
        <PortalLayout title="Agenda" subtitle="As suas marcacoes e eventos disponiveis.">
            <Head title="Agenda" />
            <div className="space-y-4">
                <h1 className="text-2xl font-semibold text-slate-900">Agenda e marcacoes</h1>
                <div className="grid gap-3">
                    {events.data.map((event) => (
                        <article key={event.id} className="rounded-2xl border border-slate-200 bg-white p-4">
                            <div className="flex items-center justify-between gap-3">
                                <Link href={route('portal.events.show', event.id)} className="font-semibold text-slate-900 hover:text-slate-700">{event.title}</Link>
                                <EventStatusBadge status={event.status} />
                            </div>
                            <div className="mt-2 flex flex-wrap items-center gap-2 text-xs">
                                <EventTypeBadge eventType={event.event_type} />
                                <span className="text-slate-600">{new Date(event.start_at).toLocaleString()} - {new Date(event.end_at).toLocaleString()}</span>
                            </div>
                        </article>
                    ))}
                    {events.data.length === 0 ? <p className="text-sm text-slate-500">Sem eventos disponiveis.</p> : null}
                </div>
            </div>
        </PortalLayout>
    );
}
