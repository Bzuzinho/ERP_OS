import EventStatusBadge from '@/Components/EventStatusBadge';
import PortalLayout from '@/Layouts/PortalLayout';
import { Head } from '@inertiajs/react';

type Props = {
    event: {
        id: number;
        title: string;
        description?: string | null;
        status: string;
        start_at: string;
        end_at: string;
        location_text?: string | null;
    };
};

export default function Show({ event }: Props) {
    return (
        <PortalLayout title="Agenda" subtitle="Eventos e marcacoes">
            <Head title={event.title} />
            <div className="rounded-2xl border border-slate-200 bg-white p-4 sm:p-6">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <h1 className="text-2xl font-semibold text-slate-900">{event.title}</h1>
                    <EventStatusBadge status={event.status} />
                </div>
                <p className="mt-3 text-sm text-slate-700">{event.description ?? 'Sem descricao.'}</p>
                <p className="mt-3 text-sm text-slate-600">{new Date(event.start_at).toLocaleString()} - {new Date(event.end_at).toLocaleString()}</p>
                <p className="text-sm text-slate-600">Local: {event.location_text ?? 'A indicar'}</p>
            </div>
        </PortalLayout>
    );
}
