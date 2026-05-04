import AppBadge from '@/Components/App/AppBadge';
import AppCard from '@/Components/App/AppCard';
import EmptyState from '@/Components/App/EmptyState';
import FloatingActionButton from '@/Components/App/FloatingActionButton';
import PortalLayout from '@/Layouts/PortalLayout';
import { Head, Link } from '@inertiajs/react';
import { useMemo } from 'react';

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
    const weekChips = useMemo(() => {
        const now = new Date();
        return Array.from({ length: 7 }).map((_, index) => {
            const day = new Date(now);
            day.setDate(now.getDate() + index);
            return day.toLocaleDateString('pt-PT', { weekday: 'short', day: '2-digit' });
        });
    }, []);

    const statusTone = (status: string): 'blue' | 'amber' | 'green' | 'red' | 'slate' => {
        const normalized = status.toLowerCase();
        if (normalized.includes('cancel') || normalized.includes('adiad')) return 'red';
        if (normalized.includes('pend')) return 'amber';
        if (normalized.includes('concl') || normalized.includes('realiz')) return 'green';
        if (normalized.includes('confirm') || normalized.includes('agend')) return 'blue';
        return 'slate';
    };

    return (
        <PortalLayout title="Agenda" subtitle="Reuniões, visitas e marcações">
            <Head title="Agenda" />
            <div className="space-y-4">
                <div className="flex gap-2 overflow-x-auto pb-1 lg:hidden">
                    {weekChips.map((chip) => (
                        <button key={chip} type="button" className="rounded-2xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600">
                            {chip}
                        </button>
                    ))}
                </div>

                <AppCard className="lg:hidden">
                    <h2 className="text-base font-bold text-slate-900">Hoje</h2>
                    <div className="mt-4 grid gap-3">
                        {events.data.slice(0, 3).map((event) => (
                            <Link key={event.id} href={route('portal.events.show', event.id)} className="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                                <div className="flex items-center justify-between gap-2">
                                    <p className="text-sm font-semibold text-slate-900">{event.title}</p>
                                    <AppBadge tone={statusTone(event.status)}>{event.status}</AppBadge>
                                </div>
                                <p className="mt-1 text-xs text-slate-500">{new Date(event.start_at).toLocaleString()}</p>
                            </Link>
                        ))}
                        {events.data.length === 0 ? <EmptyState title="Sem eventos hoje" /> : null}
                    </div>
                </AppCard>

                <AppCard className="lg:hidden">
                    <h2 className="text-base font-bold text-slate-900">Próximos dias</h2>
                    <div className="mt-4 grid gap-3">
                        {events.data.slice(3).map((event) => (
                            <Link key={event.id} href={route('portal.events.show', event.id)} className="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                                <div className="flex items-center justify-between gap-2">
                                    <p className="text-sm font-semibold text-slate-900">{event.title}</p>
                                    <AppBadge tone={statusTone(event.status)}>{event.status}</AppBadge>
                                </div>
                                <p className="mt-1 text-xs text-slate-500">{new Date(event.start_at).toLocaleString()} - {new Date(event.end_at).toLocaleString()}</p>
                            </Link>
                        ))}
                    </div>
                </AppCard>

                <div className="hidden lg:grid lg:gap-4 xl:grid-cols-2">
                    {events.data.map((event) => (
                        <Link key={event.id} href={route('portal.events.show', event.id)} className="rounded-2xl border border-slate-200/70 bg-white p-4 shadow-sm">
                            <div className="flex items-center justify-between gap-2">
                                <h3 className="text-base font-semibold text-slate-900">{event.title}</h3>
                                <AppBadge tone={statusTone(event.status)}>{event.status}</AppBadge>
                            </div>
                            <p className="mt-1 text-xs text-slate-500">{new Date(event.start_at).toLocaleString()} - {new Date(event.end_at).toLocaleString()}</p>
                            {event.related_contact?.name ? <p className="mt-2 text-sm text-slate-600">Contacto: {event.related_contact.name}</p> : null}
                            {event.related_ticket?.reference ? <p className="mt-1 text-sm text-slate-600">Pedido: {event.related_ticket.reference}</p> : null}
                        </Link>
                    ))}
                    {events.data.length === 0 ? <EmptyState title="Sem eventos" description="Não existem eventos para mostrar." /> : null}
                </div>

                {events.data.length <= 3 ? <div className="lg:hidden"><EmptyState title="Sem mais eventos" description="A agenda dos próximos dias será mostrada aqui." /></div> : null}
            </div>

            <FloatingActionButton href={route('portal.events.index')} label="Nova marcação" />
        </PortalLayout>
    );
}
