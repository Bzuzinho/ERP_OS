import EventParticipantsList from '@/Components/EventParticipantsList';
import EventStatusBadge from '@/Components/EventStatusBadge';
import EventTypeBadge from '@/Components/EventTypeBadge';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link } from '@inertiajs/react';

type Props = {
    event: {
        id: number;
        title: string;
        description?: string | null;
        event_type: string;
        status: string;
        visibility: string;
        start_at: string;
        end_at: string;
        location_text?: string | null;
        relatedTicket?: { id: number; reference: string; title: string } | null;
        relatedContact?: { id: number; name: string } | null;
        participants: { id: number; role: string | null; attendance_status: string; user?: { id: number; name: string } | null; contact?: { id: number; name: string } | null }[];
    };
};

export default function Show({ event }: Props) {
    return (
        <AdminLayout title={event.title} subtitle="Detalhe do evento, visibilidade e participantes.">
            <Head title={event.title} />
            <div className="space-y-5">
                <div className="rounded-2xl border border-slate-200 bg-white p-6">
                    <div className="flex flex-wrap items-center justify-between gap-3">
                        <h1 className="text-2xl font-semibold text-slate-900">{event.title}</h1>
                        <div className="flex items-center gap-2">
                            <EventStatusBadge status={event.status} />
                            <EventTypeBadge eventType={event.event_type} />
                        </div>
                    </div>
                    <p className="mt-3 text-sm text-slate-700">{event.description ?? 'Sem descricao.'}</p>
                    <p className="mt-3 text-sm text-slate-600">{new Date(event.start_at).toLocaleString()} - {new Date(event.end_at).toLocaleString()}</p>
                    <p className="text-sm text-slate-600">Local: {event.location_text ?? 'N/A'}</p>
                    <p className="text-sm text-slate-600">Visibilidade: {event.visibility}</p>
                    <p className="text-sm text-slate-600">Ticket: {event.relatedTicket ? `${event.relatedTicket.reference} - ${event.relatedTicket.title}` : 'N/A'}</p>
                    <p className="text-sm text-slate-600">Contacto: {event.relatedContact?.name ?? 'N/A'}</p>
                    <Link href={route('admin.events.edit', event.id)} className="mt-4 inline-flex rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Editar</Link>
                </div>

                <EventParticipantsList participants={event.participants} />
            </div>
        </AdminLayout>
    );
}
