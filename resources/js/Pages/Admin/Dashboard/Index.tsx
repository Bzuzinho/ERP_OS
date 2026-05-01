import DashboardSection from '@/Components/Reports/DashboardSection';
import KpiCard from '@/Components/Reports/KpiCard';
import KpiGrid from '@/Components/Reports/KpiGrid';
import MetricList from '@/Components/Reports/MetricList';
import StatusBreakdown from '@/Components/Reports/StatusBreakdown';
import AdminLayout from '@/Layouts/AdminLayout';

type AdminDashboardProps = {
    data: {
        kpis: Record<string, number | string | null>;
        ticket_status_breakdown: Record<string, number>;
        ticket_category_breakdown: Record<string, number>;
        recent_tickets: Array<Record<string, unknown>>;
        pending_tasks: Array<Record<string, unknown>>;
        today_events: Array<Record<string, unknown>>;
        today_reservations: Array<Record<string, unknown>>;
        low_stock_items: Array<Record<string, unknown>>;
        today_absences: Array<Record<string, unknown>>;
        active_plans: Array<Record<string, unknown>>;
        upcoming_public_activities: Array<Record<string, unknown>>;
        meeting_minutes: Record<string, number>;
    };
};

export default function AdminDashboard({ data }: AdminDashboardProps) {
    const todayAbsences = data.today_absences as Array<{ id: number; status?: string; employee?: { employee_number?: string } }>;

    const primaryKpis: Array<[string, string | number | null]> = [
        ['Pedidos em aberto', data.kpis.open_tickets],
        ['Pedidos urgentes', data.kpis.urgent_tickets],
        ['Pedidos atrasados', data.kpis.overdue_tickets],
        ['Tickets fechados este mês', data.kpis.closed_tickets_this_month],
        ['Tarefas pendentes', data.kpis.pending_tasks],
        ['Tarefas em curso', data.kpis.in_progress_tasks],
        ['Tarefas concluídas mês', data.kpis.done_tasks_this_month],
        ['Eventos hoje', data.kpis.events_today],
        ['Reservas hoje', data.kpis.reservations_today],
        ['Reservas pendentes', data.kpis.pending_reservations],
        ['Stock baixo', data.kpis.low_stock_items],
        ['Empréstimos em atraso', data.kpis.overdue_loans],
        ['Presentes hoje', data.kpis.present_employees_today],
        ['Ausências hoje', data.kpis.absences_today],
        ['Planos em execução', data.kpis.plans_in_execution],
        ['Planos pendentes aprovação', data.kpis.plans_pending_approval],
        ['Documentos ativos', data.kpis.active_documents],
    ];

    return (
        <AdminLayout title="Dashboard Administrativo" subtitle="KPIs operacionais e visão consolidada por módulo">
            <KpiGrid>
                {primaryKpis.map(([label, value]) => (
                    <KpiCard key={label} label={label} value={String(value ?? '-')} />
                ))}
            </KpiGrid>

            <div className="mt-4 grid gap-4 xl:grid-cols-2">
                <DashboardSection title="Pedidos por Estado">
                    <StatusBreakdown values={data.ticket_status_breakdown} />
                </DashboardSection>
                <DashboardSection title="Pedidos por Categoria">
                    <StatusBreakdown values={data.ticket_category_breakdown} />
                </DashboardSection>
            </div>

            <div className="mt-4 grid gap-4 xl:grid-cols-3">
                <DashboardSection title="Pedidos Recentes">
                    <MetricList
                        values={Object.fromEntries(
                            data.recent_tickets.slice(0, 8).map((ticket) => [
                                String(ticket.reference ?? `#${ticket.id}`),
                                String(ticket.status ?? '-'),
                            ]),
                        )}
                    />
                </DashboardSection>
                <DashboardSection title="Tarefas Pendentes">
                    <MetricList
                        values={Object.fromEntries(
                            data.pending_tasks.slice(0, 8).map((task) => [String(task.title ?? `#${task.id}`), String(task.status ?? '-')]),
                        )}
                    />
                </DashboardSection>
                <DashboardSection title="Agenda de Hoje">
                    <MetricList
                        values={Object.fromEntries(
                            data.today_events.slice(0, 8).map((event) => [String(event.title ?? `#${event.id}`), String(event.status ?? '-')]),
                        )}
                    />
                </DashboardSection>
            </div>

            <div className="mt-4 grid gap-4 xl:grid-cols-3">
                <DashboardSection title="Reservas de Hoje">
                    <MetricList
                        values={Object.fromEntries(
                            data.today_reservations.slice(0, 8).map((reservation) => [
                                String(reservation.purpose ?? `#${reservation.id}`),
                                String(reservation.status ?? '-'),
                            ]),
                        )}
                    />
                </DashboardSection>
                <DashboardSection title="Alertas de Stock">
                    <MetricList
                        values={Object.fromEntries(
                            data.low_stock_items.slice(0, 8).map((item) => [
                                String(item.name ?? `#${item.id}`),
                                `${String(item.current_stock ?? '-')} / min ${String(item.minimum_stock ?? '-')}`,
                            ]),
                        )}
                    />
                </DashboardSection>
                <DashboardSection title="Ausências de Hoje">
                    <MetricList
                        values={Object.fromEntries(
                            todayAbsences.slice(0, 8).map((absence) => [
                                String(absence.employee?.employee_number ?? `#${absence.id}`),
                                String(absence.status ?? '-'),
                            ]),
                        )}
                    />
                </DashboardSection>
            </div>

            <div className="mt-4 grid gap-4 xl:grid-cols-2">
                <DashboardSection title="Planos em Execução">
                    <MetricList
                        values={Object.fromEntries(
                            data.active_plans.slice(0, 8).map((plan) => [
                                String(plan.title ?? `#${plan.id}`),
                                `${String(plan.status ?? '-')} (${String(plan.progress_percent ?? 0)}%)`,
                            ]),
                        )}
                    />
                </DashboardSection>
                <DashboardSection title="Atividades Públicas Próximas">
                    <MetricList
                        values={Object.fromEntries(
                            data.upcoming_public_activities.slice(0, 8).map((activity) => [
                                String(activity.title ?? `#${activity.id}`),
                                String(activity.plan_type ?? '-'),
                            ]),
                        )}
                    />
                </DashboardSection>
            </div>
        </AdminLayout>
    );
}
