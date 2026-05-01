import DashboardSection from '@/Components/Reports/DashboardSection';
import KpiCard from '@/Components/Reports/KpiCard';
import KpiGrid from '@/Components/Reports/KpiGrid';
import MetricList from '@/Components/Reports/MetricList';
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
            <KpiGrid>
                <KpiCard label="Os meus pedidos ativos" value={data.kpis.my_active_tickets} />
                <KpiCard label="Aguardando resposta" value={data.kpis.waiting_tickets} />
                <KpiCard label="Próximas marcações" value={data.kpis.upcoming_events} />
                <KpiCard label="Próximas reservas" value={data.kpis.upcoming_reservations} />
                <KpiCard label="Documentos disponíveis" value={data.kpis.available_documents} />
            </KpiGrid>

            <div className="mt-4 grid gap-4 xl:grid-cols-2">
                <DashboardSection title="Os meus pedidos ativos">
                    <MetricList
                        values={Object.fromEntries(
                            data.tickets.active.slice(0, 8).map((ticket) => [
                                String(ticket.reference ?? `#${ticket.id}`),
                                String(ticket.status ?? '-'),
                            ]),
                        )}
                    />
                </DashboardSection>
                <DashboardSection title="Últimos pedidos atualizados">
                    <MetricList
                        values={Object.fromEntries(
                            data.tickets.recent_updates.slice(0, 8).map((ticket) => [
                                String(ticket.reference ?? `#${ticket.id}`),
                                String(ticket.updated_at ?? '-'),
                            ]),
                        )}
                    />
                </DashboardSection>
            </div>

            <div className="mt-4 grid gap-4 xl:grid-cols-2">
                <DashboardSection title="Próximas marcações/eventos associados">
                    <MetricList
                        values={Object.fromEntries(
                            data.events.slice(0, 8).map((event) => [
                                String(event.title ?? `#${event.id}`),
                                String(event.start_at ?? '-'),
                            ]),
                        )}
                    />
                </DashboardSection>
                <DashboardSection title="Próximas reservas">
                    <MetricList
                        values={Object.fromEntries(
                            data.reservations.slice(0, 8).map((reservation) => [
                                String(reservation.purpose ?? `#${reservation.id}`),
                                String(reservation.start_at ?? '-'),
                            ]),
                        )}
                    />
                </DashboardSection>
            </div>

            <div className="mt-4 grid gap-4 xl:grid-cols-2">
                <DashboardSection title="Documentos disponíveis">
                    <MetricList
                        values={Object.fromEntries(
                            data.documents.slice(0, 8).map((document) => [
                                String(document.title ?? `#${document.id}`),
                                String(document.visibility ?? '-'),
                            ]),
                        )}
                    />
                </DashboardSection>
                <DashboardSection title="Atividades públicas próximas">
                    <MetricList
                        values={Object.fromEntries(
                            data.public_plans.slice(0, 8).map((plan) => [
                                String(plan.title ?? `#${plan.id}`),
                                String(plan.start_date ?? '-'),
                            ]),
                        )}
                    />
                </DashboardSection>
            </div>
        </PortalLayout>
    );
}
