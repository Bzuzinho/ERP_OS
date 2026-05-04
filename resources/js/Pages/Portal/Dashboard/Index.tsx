import AppBadge from '@/Components/App/AppBadge';
import AppCard from '@/Components/App/AppCard';
import EmptyState from '@/Components/App/EmptyState';
import KpiCard from '@/Components/App/KpiCard';
import SectionTitle from '@/Components/App/SectionTitle';
import PortalLayout from '@/Layouts/PortalLayout';

type PortalDashboardProps = {
    data: {
        kpis: Record<string, number>;
        tickets: {
            active: Array<Record<string, unknown>>;
            recent_updates: Array<Record<string, unknown>>;
        };
        events: Array<Record<string, unknown>>;
        reservations: Array<Record<string, unknown>>;
        documents: Array<Record<string, unknown>>;
        public_plans: Array<Record<string, unknown>>;
    };
};

export default function PortalDashboard({ data }: PortalDashboardProps) {
    return (
        <PortalLayout title="Dashboard" subtitle="A sua atividade no portal em tempo real">
            <div className="grid grid-cols-2 gap-3 md:grid-cols-4 md:gap-4">
                <KpiCard label="Os meus pedidos ativos" value={data.kpis.my_active_tickets} />
                <KpiCard label="Aguardando resposta" value={data.kpis.waiting_tickets} />
                <KpiCard label="Próximas marcações" value={data.kpis.upcoming_events} />
                <KpiCard label="Próximas reservas" value={data.kpis.upcoming_reservations} />
                <KpiCard label="Documentos disponíveis" value={data.kpis.available_documents} />
            </div>

            <div className="mt-5 grid gap-4 xl:grid-cols-2">
                <AppCard>
                    <SectionTitle title="Os meus pedidos ativos" subtitle="Acompanhe as suas ocorrências" />
                    <div className="mt-4 space-y-3">
                        {data.tickets.active.slice(0, 6).map((ticket) => (
                            <div key={String(ticket.id)} className="flex items-center justify-between rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                                <div>
                                    <p className="text-sm font-semibold text-blue-700">{String(ticket.reference ?? `#${ticket.id}`)}</p>
                                    <p className="text-xs text-slate-500">{String(ticket.title ?? '-')}</p>
                                </div>
                                <AppBadge tone="blue">{String(ticket.status ?? '-')}</AppBadge>
                            </div>
                        ))}
                        {data.tickets.active.length === 0 ? <EmptyState title="Sem pedidos ativos" /> : null}
                    </div>
                </AppCard>

                <AppCard>
                    <SectionTitle title="Agenda" subtitle="Próximas marcações e eventos" />
                    <div className="mt-4 space-y-3">
                        {data.events.slice(0, 6).map((event) => (
                            <div key={String(event.id)} className="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                                <p className="text-sm font-semibold text-slate-900">{String(event.title ?? `#${event.id}`)}</p>
                                <p className="mt-1 text-xs text-slate-500">{String(event.start_at ?? '-')}</p>
                            </div>
                        ))}
                        {data.events.length === 0 ? <EmptyState title="Sem eventos próximos" /> : null}
                    </div>
                </AppCard>
            </div>

            <div className="mt-4 grid gap-4 xl:grid-cols-2">
                <AppCard>
                    <SectionTitle title="Documentos disponíveis" subtitle="Ficheiros partilhados pela junta" />
                    <div className="mt-4 space-y-3">
                        {data.documents.slice(0, 6).map((document) => (
                            <div key={String(document.id)} className="flex items-center justify-between rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                                <p className="text-sm font-semibold text-slate-900">{String(document.title ?? `#${document.id}`)}</p>
                                <AppBadge tone="indigo">{String(document.visibility ?? '-')}</AppBadge>
                            </div>
                        ))}
                        {data.documents.length === 0 ? <EmptyState title="Sem documentos" /> : null}
                    </div>
                </AppCard>

                <AppCard>
                    <SectionTitle title="Atividades públicas" subtitle="Próximas ações no território" />
                    <div className="mt-4 space-y-3">
                        {data.public_plans.slice(0, 6).map((plan) => (
                            <div key={String(plan.id)} className="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                                <p className="text-sm font-semibold text-slate-900">{String(plan.title ?? `#${plan.id}`)}</p>
                                <p className="mt-1 text-xs text-slate-500">{String(plan.start_date ?? '-')}</p>
                            </div>
                        ))}
                        {data.public_plans.length === 0 ? <EmptyState title="Sem atividades" /> : null}
                    </div>
                </AppCard>
            </div>
        </PortalLayout>
    );
}
