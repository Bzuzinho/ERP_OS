import AppBadge from '@/Components/App/AppBadge';
import AppCard from '@/Components/App/AppCard';
import EmptyState from '@/Components/App/EmptyState';
import PortalLayout from '@/Layouts/PortalLayout';
import { Head, Link } from '@inertiajs/react';

type EventRow = {
    id: number;
    title: string;
    start_at: string;
    end_at: string;
    status: string;
    description?: string | null;
    location_text?: string | null;
};

type Props = {
    events: { data: EventRow[] };
};

const statusLabel: Record<string, string> = {
    scheduled: 'Agendado',
    confirmed: 'Confirmado',
    cancelled: 'Cancelado',
    completed: 'Concluido',
};

const statusTone: Record<string, 'blue' | 'amber' | 'green' | 'red' | 'slate'> = {
    scheduled: 'blue',
    confirmed: 'green',
    cancelled: 'red',
    completed: 'slate',
};

export default function Index({ events }: Props) {
    return (
        <PortalLayout title="Agenda" subtitle="Eventos e marcacoes.">
            <Head title="Agenda" />

            <div className="grid gap-4 xl:grid-cols-2">
                {events.data.map((event) => (
                    <Link key={event.id} href={route('portal.events.show', event.id)} className="rounded-2xl border border-slate-200/70 bg-white p-4 shadow-sm">
                        <div className="flex items-center justify-between gap-2">
                            <h3 className="text-base font-semibold text-slate-900">{event.title}</h3>
                            <AppBadge tone={statusTone[event.status] ?? 'slate'}>{statusLabel[event.status] ?? 'Em analise'}</AppBadge>
                        </div>
                        <p className="mt-2 text-sm text-slate-600">{new Date(event.start_at).toLocaleString()} - {new Date(event.end_at).toLocaleString()}</p>
                        <p className="mt-1 text-sm text-slate-600">Local: {event.location_text ?? 'A indicar'}</p>
                        {event.description ? <p className="mt-2 line-clamp-2 text-sm text-slate-600">{event.description}</p> : null}
                    </Link>
                ))}
                {events.data.length === 0 ? <EmptyState title="Sem eventos" description="Nao existem eventos para mostrar." /> : null}
            </div>
        </PortalLayout>
    );
}
