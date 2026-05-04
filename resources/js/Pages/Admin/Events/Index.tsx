import AgendaDayList from '@/Components/AgendaDayList';
import AppBadge from '@/Components/App/AppBadge';
import AppCard from '@/Components/App/AppCard';
import EmptyState from '@/Components/App/EmptyState';
import FilterPills from '@/Components/App/FilterPills';
import FloatingActionButton from '@/Components/App/FloatingActionButton';
import AdminLayout from '@/Layouts/AdminLayout';
import SearchInput from '@/Components/App/SearchInput';
import { Head, Link, router } from '@inertiajs/react';
import { useMemo, useState } from 'react';

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
    const [statusFilter, setStatusFilter] = useState(filters.status ?? '');
    const [search, setSearch] = useState(filters.search ?? '');
    const [eventTypeFilter, setEventTypeFilter] = useState(filters.eventType ?? '');

    const weekChips = useMemo(() => {
        const now = new Date();
        return Array.from({ length: 7 }).map((_, index) => {
            const day = new Date(now);
            day.setDate(now.getDate() + index);
            return day.toLocaleDateString('pt-PT', { weekday: 'short', day: '2-digit' });
        });
    }, []);

    const filteredEvents = events.data.filter((event) => {
        if (!statusFilter) return true;
        return event.status === statusFilter;
    });

    const applyDesktopFilters = () => {
        router.get(
            route('admin.events.index'),
            {
                search: search || undefined,
                status: statusFilter || undefined,
                eventType: eventTypeFilter || undefined,
                date: filters.date || undefined,
            },
            { preserveState: true, replace: true },
        );
    };

    const statusTone = (status: string): 'blue' | 'amber' | 'green' | 'red' | 'slate' => {
        const normalized = status.toLowerCase();
        if (normalized.includes('cancel') || normalized.includes('adiad')) return 'red';
        if (normalized.includes('pend')) return 'amber';
        if (normalized.includes('concl') || normalized.includes('realiz')) return 'green';
        if (normalized.includes('confirm') || normalized.includes('agend')) return 'blue';
        return 'slate';
    };

    return (
        <AdminLayout title="Agenda" subtitle="Reuniões, visitas e marcações">
            <Head title="Agenda" />
            <div className="space-y-6">
                <div className="flex gap-2 overflow-x-auto pb-1 lg:hidden">
                    {weekChips.map((chip) => (
                        <button key={chip} type="button" className="rounded-2xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600">
                            {chip}
                        </button>
                    ))}
                </div>

                <div className="lg:hidden">
                    <FilterPills
                        selected={statusFilter}
                        onChange={setStatusFilter}
                        options={[
                            { label: 'Todos', value: '' },
                            ...statuses.slice(0, 4).map((status) => ({ label: status, value: status })),
                        ]}
                    />
                </div>

                <AppCard className="hidden lg:block">
                    <div className="grid gap-3 md:grid-cols-4">
                        <SearchInput value={search} onChange={setSearch} placeholder="Pesquisar reunião, visita ou local" />
                        <select value={statusFilter} onChange={(event) => setStatusFilter(event.target.value)} className="rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700">
                            <option value="">Todos os estados</option>
                            {statuses.map((status) => (
                                <option key={status} value={status}>{status}</option>
                            ))}
                        </select>
                        <select value={eventTypeFilter} onChange={(event) => setEventTypeFilter(event.target.value)} className="rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700">
                            <option value="">Todos os tipos</option>
                            {eventTypes.map((eventType) => (
                                <option key={eventType} value={eventType}>{eventType}</option>
                            ))}
                        </select>
                        <button type="button" onClick={applyDesktopFilters} className="rounded-2xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                            Filtrar agenda
                        </button>
                    </div>
                </AppCard>

                <AppCard className="lg:hidden">
                    <h2 className="text-base font-bold text-slate-900">Hoje</h2>
                    <div className="mt-4 grid gap-3">
                        {dayList.slice(0, 5).map((event) => (
                            <Link key={event.id} href={route('admin.events.show', event.id)} className="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                                <div className="flex items-center justify-between gap-2">
                                    <p className="text-sm font-semibold text-slate-900">{event.title}</p>
                                    <AppBadge tone={statusTone(event.status)}>{event.status}</AppBadge>
                                </div>
                                <p className="mt-1 text-xs text-slate-500">{new Date(event.start_at).toLocaleString()}</p>
                            </Link>
                        ))}
                        {dayList.length === 0 ? <EmptyState title="Sem agenda para hoje" /> : null}
                    </div>
                </AppCard>

                <AppCard className="lg:hidden">
                    <h2 className="text-base font-bold text-slate-900">Próximos dias</h2>
                    <div className="mt-4 grid gap-3">
                        {filteredEvents.slice(0, 10).map((event) => (
                            <Link key={event.id} href={route('admin.events.show', event.id)} className="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                                <div className="flex items-center justify-between gap-2">
                                    <p className="text-sm font-semibold text-slate-900">{event.title}</p>
                                    <AppBadge tone={statusTone(event.status)}>{event.status}</AppBadge>
                                </div>
                                <p className="mt-1 text-xs text-slate-500">{new Date(event.start_at).toLocaleString()} - {new Date(event.end_at).toLocaleString()}</p>
                                {event.location_text ? <p className="mt-1 text-xs text-slate-400">{event.location_text}</p> : null}
                            </Link>
                        ))}
                        {filteredEvents.length === 0 ? <EmptyState title="Sem eventos" description="Não existem eventos para os filtros atuais." /> : null}
                    </div>
                </AppCard>

                <div className="hidden lg:grid lg:gap-4 xl:grid-cols-2">
                    {filteredEvents.slice(0, 12).map((event) => (
                        <Link key={event.id} href={route('admin.events.show', event.id)} className="rounded-2xl border border-slate-200/70 bg-white p-4 shadow-sm">
                            <div className="flex items-center justify-between gap-2">
                                <h3 className="text-base font-semibold text-slate-900">{event.title}</h3>
                                <AppBadge tone={statusTone(event.status)}>{event.status}</AppBadge>
                            </div>
                            <div className="mt-2 flex items-center gap-2 text-xs text-slate-500">
                                <span>{new Date(event.start_at).toLocaleDateString()}</span>
                                <span>•</span>
                                <span>{new Date(event.start_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</span>
                                <span>•</span>
                                <span>{event.event_type}</span>
                            </div>
                            {event.location_text ? <p className="mt-1 text-sm text-slate-500">{event.location_text}</p> : null}
                        </Link>
                    ))}
                    {filteredEvents.length === 0 ? <EmptyState title="Sem eventos" description="Não existem eventos para os filtros atuais." /> : null}
                </div>

                <div className="hidden lg:block">
                    <AgendaDayList events={dayList} />
                </div>
            </div>

            <FloatingActionButton href={route('admin.events.create')} label="Novo evento" />
        </AdminLayout>
    );
}
