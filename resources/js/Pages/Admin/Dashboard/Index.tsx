import AppBadge from '@/Components/App/AppBadge';
import AppCard from '@/Components/App/AppCard';
import EmptyState from '@/Components/App/EmptyState';
import AdminLayout from '@/Layouts/AdminLayout';
import { router } from '@inertiajs/react';
import { Link } from '@inertiajs/react';
import { useEffect, useState } from 'react';

// ─────────────────────────────────────────────────────────────────────────────
// Types
// ─────────────────────────────────────────────────────────────────────────────

type ServiceArea = { id: number; name: string; parent_id?: number | null };
type Department = { id: number; name: string; service_areas: ServiceArea[] };
type OrgNode = { id: number; name: string; is_default: boolean; has_global_access: boolean; departments: Department[] };
type ContextTree = { organizations: OrgNode[] };

type Filters = {
    all_my_scopes?: boolean | string;
    organization_id?: number | null;
    department_id?: number | null;
    service_area_id?: number | null;
};

type TaskItem = {
    id: number;
    title?: string;
    status?: string;
    priority?: string;
    due_date?: string | null;
    assigned_to?: number | null;
    ticket_id?: number | null;
    is_overdue?: boolean;
    assignee?: { name?: string } | null;
    ticket?: { id?: number; reference?: string; title?: string } | null;
};

type TicketItem = {
    id: number;
    reference?: string;
    title?: string;
    status?: string;
    priority?: string;
    category?: string;
    assigned_to?: number | null;
    due_date?: string | null;
    assignee?: { name?: string } | null;
};

type PlanItem = {
    id: number;
    title?: string;
    plan_type?: string;
    status?: string;
    progress_percent?: number | null;
    start_date?: string | null;
    end_date?: string | null;
    owner?: { name?: string } | null;
};

type ReservationItem = {
    id: number;
    purpose?: string;
    status?: string;
    start_at?: string;
    end_at?: string;
    space?: { name?: string; status?: string } | null;
};

type ActivityItem = {
    id: number;
    title?: string;
    _type: 'event' | 'reservation' | 'plan';
    start_at?: string;
    start_date?: string;
    status?: string;
    location_text?: string;
};

type AlertCounts = {
    overdue_tasks: number;
    reopened_tasks: number;
    pending_validation_tasks: number;
    awaiting_validation_tickets: number;
    pending_reservations: number;
};

type Operational = {
    resumo: Record<string, number>;
    agenda_hoje: {
        events: Array<{ id: number; title?: string; start_at?: string; location_text?: string; status?: string }>;
        reservations: ReservationItem[];
        tasks: TaskItem[];
    };
    proximas_atividades: ActivityItem[];
    alertas: {
        overdue_tasks: TaskItem[];
        reopened_tasks: TaskItem[];
        pending_validation_tasks: TaskItem[];
        awaiting_validation_tickets: TicketItem[];
        pending_reservations: ReservationItem[];
        counts: AlertCounts;
    };
    espacos: { today: ReservationItem[]; pending: ReservationItem[]; counts: Record<string, number> };
    recursos_humanos: {
        absences: Array<{ id: number; status?: string; employee?: { employee_number?: string } }>;
        task_summary: Array<{ assigned_to: number; task_count: number; assignee?: { name?: string } | null }>;
        counts: Record<string, number>;
    };
    pedidos_recentes: TicketItem[];
    tarefas_em_curso: TaskItem[];
    tarefas_por_validar: TaskItem[];
    planos_operacionais: PlanItem[];
};

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
    filters: Filters;
    operational: Operational;
    contextTree: ContextTree;
};

// ─────────────────────────────────────────────────────────────────────────────
// Helpers
// ─────────────────────────────────────────────────────────────────────────────

function statusTone(status: string): 'blue' | 'amber' | 'green' | 'red' | 'slate' {
    const s = status.toLowerCase();
    if (s.includes('resol') || s.includes('fech') || s.includes('valid')) return 'green';
    if (s.includes('urgent') || s.includes('alta') || s.includes('reopen') || s.includes('reaber')) return 'red';
    if (s.includes('pend') || s.includes('anal') || s.includes('espera') || s.includes('waiting')) return 'amber';
    if (s.includes('novo') || s.includes('exec') || s.includes('abert') || s.includes('progress')) return 'blue';
    return 'slate';
}

function fmtTime(dt?: string | null) {
    if (!dt) return '--:--';
    return new Date(dt).toLocaleTimeString('pt-PT', { hour: '2-digit', minute: '2-digit' });
}

function fmtDate(dt?: string | null) {
    if (!dt) return '-';
    return new Date(dt).toLocaleDateString('pt-PT');
}

function typeBadge(type: string) {
    const map: Record<string, string> = { event: 'Evento', reservation: 'Reserva', plan: 'Plano' };
    return map[type] ?? type;
}

// ─────────────────────────────────────────────────────────────────────────────
// Context Selector
// ─────────────────────────────────────────────────────────────────────────────

function ContextSelector({ contextTree, filters }: { contextTree: ContextTree; filters: Filters }) {
    const [orgId, setOrgId] = useState<number | null>(filters.organization_id ?? null);
    const [deptId, setDeptId] = useState<number | null>(filters.department_id ?? null);
    const [areaId, setAreaId] = useState<number | null>(filters.service_area_id ?? null);
    const [allScopes, setAllScopes] = useState<boolean>(!!(filters.all_my_scopes));

    const selectedOrg = contextTree.organizations.find((o) => o.id === orgId) ?? null;
    const selectedDept = selectedOrg?.departments.find((d) => d.id === deptId) ?? null;

    useEffect(() => {
        if (allScopes) { setOrgId(null); setDeptId(null); setAreaId(null); }
    }, [allScopes]);

    function applyFilter() {
        router.get(
            route('admin.dashboard'),
            {
                all_my_scopes: allScopes ? '1' : undefined,
                organization_id: allScopes ? undefined : (orgId ?? undefined),
                department_id: allScopes ? undefined : (deptId ?? undefined),
                service_area_id: allScopes ? undefined : (areaId ?? undefined),
            },
            { preserveScroll: true, replace: true },
        );
    }

    return (
        <AppCard className="mb-4">
            <div className="flex flex-wrap items-end gap-3">
                <div className="flex flex-col gap-1">
                    <span className="text-xs font-medium text-slate-500">Âmbito</span>
                    <div className="flex gap-2">
                        <button type="button" onClick={() => setAllScopes(true)}
                            className={`rounded-lg px-3 py-1.5 text-xs font-semibold transition ${allScopes ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'}`}>
                            Tudo o que me diz respeito
                        </button>
                        <button type="button" onClick={() => setAllScopes(false)}
                            className={`rounded-lg px-3 py-1.5 text-xs font-semibold transition ${!allScopes ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'}`}>
                            Filtrar por contexto
                        </button>
                    </div>
                </div>

                {!allScopes && (
                    <>
                        <div className="flex flex-col gap-1">
                            <label className="text-xs font-medium text-slate-500">Organização</label>
                            <select value={orgId ?? ''} onChange={(e) => { const v = e.target.value ? Number(e.target.value) : null; setOrgId(v); setDeptId(null); setAreaId(null); }}
                                className="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">— Selecionar —</option>
                                {contextTree.organizations.map((o) => <option key={o.id} value={o.id}>{o.name}</option>)}
                            </select>
                        </div>

                        {selectedOrg && selectedOrg.departments.length > 0 && (
                            <div className="flex flex-col gap-1">
                                <label className="text-xs font-medium text-slate-500">Departamento</label>
                                <select value={deptId ?? ''} onChange={(e) => { const v = e.target.value ? Number(e.target.value) : null; setDeptId(v); setAreaId(null); }}
                                    className="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">— Todos —</option>
                                    {selectedOrg.departments.map((d) => <option key={d.id} value={d.id}>{d.name}</option>)}
                                </select>
                            </div>
                        )}

                        {selectedDept && selectedDept.service_areas.length > 0 && (
                            <div className="flex flex-col gap-1">
                                <label className="text-xs font-medium text-slate-500">Tema / Área</label>
                                <select value={areaId ?? ''} onChange={(e) => setAreaId(e.target.value ? Number(e.target.value) : null)}
                                    className="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">— Todos —</option>
                                    {selectedDept.service_areas.map((a) => <option key={a.id} value={a.id}>{a.name}</option>)}
                                </select>
                            </div>
                        )}
                    </>
                )}

                <button type="button" onClick={applyFilter}
                    className="rounded-lg bg-blue-600 px-4 py-1.5 text-xs font-semibold text-white transition hover:bg-blue-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500">
                    Aplicar
                </button>

                {(orgId || deptId || areaId || allScopes) && (
                    <button type="button" onClick={() => { setAllScopes(false); setOrgId(null); setDeptId(null); setAreaId(null); router.get(route('admin.dashboard'), {}, { preserveScroll: true, replace: true }); }}
                        className="text-xs text-slate-400 hover:text-slate-600">
                        Limpar
                    </button>
                )}
            </div>
        </AppCard>
    );
}

// ─────────────────────────────────────────────────────────────────────────────
// Main Component
// ─────────────────────────────────────────────────────────────────────────────

export default function AdminDashboard({ data, filters, operational, contextTree }: AdminDashboardProps) {
    const recentTickets = data.recent_tickets as TicketItem[];
    const lowStockItems = data.low_stock_items as Array<{ id: number; name?: string; minimum_stock?: number; current_stock?: number }>;

    const resumo = operational.resumo;
    const alertCounts = operational.alertas.counts;
    const totalAlerts = alertCounts.overdue_tasks + alertCounts.reopened_tasks + alertCounts.awaiting_validation_tickets + alertCounts.pending_reservations;

    const toneStyles: Record<string, string> = {
        blue: 'bg-blue-50 text-blue-700',
        red: 'bg-rose-50 text-rose-700',
        green: 'bg-emerald-50 text-emerald-700',
        amber: 'bg-amber-50 text-amber-700',
        indigo: 'bg-indigo-50 text-indigo-700',
        violet: 'bg-violet-50 text-violet-700',
    };

    const kpis = [
        { label: 'Pedidos em aberto', value: String(resumo.open_tickets ?? 0), trend: `${String(resumo.overdue_tickets ?? 0)} com prazo crítico`, tone: 'blue', href: route('admin.tickets.index') },
        { label: 'Urgentes', value: String(resumo.urgent_tickets ?? 0), trend: 'Prioridade alta', tone: 'red', href: route('admin.tickets.index') },
        { label: 'Tarefas em curso', value: String(resumo.tasks_in_progress ?? 0), trend: `${String(resumo.tasks_overdue ?? 0)} atrasadas`, tone: (resumo.tasks_overdue ?? 0) > 0 ? 'amber' : 'blue', href: route('admin.tasks.index') },
        { label: 'Por validar', value: String(resumo.tasks_pending_validation ?? 0), trend: `${String(resumo.tasks_reopened ?? 0)} reabertas`, tone: (resumo.tasks_pending_validation ?? 0) > 0 ? 'violet' : 'green', href: route('admin.tasks.index') },
        { label: 'Reservas hoje', value: String(resumo.reservations_today ?? 0), trend: `${String(resumo.pending_reservations ?? 0)} pendentes`, tone: 'green', href: route('admin.space-reservations.index') },
        { label: 'Alertas', value: String(totalAlerts), trend: `${String(alertCounts.awaiting_validation_tickets ?? 0)} pedidos aguardam validação`, tone: totalAlerts > 0 ? 'red' : 'green', href: route('admin.tickets.index') },
    ];

    return (
        <AdminLayout title="Dashboard Operacional" subtitle="Visão geral da operação da junta">
            <ContextSelector contextTree={contextTree} filters={filters} />

            {/* KPI Row */}
            <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                {kpis.map((kpi) => (
                    <Link key={kpi.label} href={kpi.href} className="group block focus-visible:outline-none">
                        <AppCard className="rounded-2xl p-4 transition-transform duration-150 group-hover:-translate-y-0.5 group-hover:shadow-md">
                            <p className="mt-3 text-[13px] font-medium text-slate-600">{kpi.label}</p>
                            <p className="mt-1 text-2xl font-bold text-slate-950 sm:text-3xl">{kpi.value}</p>
                            <p className="mt-2 text-xs text-slate-500">{kpi.trend}</p>
                        </AppCard>
                    </Link>
                ))}
            </div>

            {/* Row 1: Pedidos Recentes + Agenda Hoje */}
            <div className="mt-4 grid w-full gap-4 lg:grid-cols-3">
                <AppCard className="lg:col-span-2">
                    <div className="mb-3 flex items-center justify-between">
                        <h2 className="text-lg font-semibold text-slate-950">Pedidos Recentes</h2>
                        <Link href={route('admin.tickets.index')} className="text-sm font-medium text-blue-600 hover:underline">Ver todos</Link>
                    </div>
                    <div className="hidden overflow-x-auto rounded-2xl border border-slate-200 xl:block">
                        <table className="min-w-full text-sm">
                            <thead className="bg-slate-50 text-left text-slate-500">
                                <tr>
                                    <th className="px-4 py-3">Ref.</th><th className="px-4 py-3">Assunto</th><th className="px-4 py-3">Categoria</th>
                                    <th className="px-4 py-3">Estado</th><th className="px-4 py-3">Prioridade</th><th className="px-4 py-3">Responsável</th><th className="px-4 py-3">Prazo</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {recentTickets.slice(0, 7).map((ticket) => (
                                    <tr key={ticket.id}>
                                        <td className="px-4 py-3"><Link href={route('admin.tickets.show', ticket.id)} className="font-semibold text-blue-700 hover:underline">{ticket.reference ?? `#${ticket.id}`}</Link></td>
                                        <td className="px-4 py-3 text-slate-800">{ticket.title ?? '-'}</td>
                                        <td className="px-4 py-3 text-slate-500">{ticket.category ?? '-'}</td>
                                        <td className="px-4 py-3"><AppBadge tone={statusTone(String(ticket.status ?? ''))}>{ticket.status ?? '-'}</AppBadge></td>
                                        <td className="px-4 py-3"><AppBadge tone={statusTone(String(ticket.priority ?? ''))}>{ticket.priority ?? '-'}</AppBadge></td>
                                        <td className="px-4 py-3 text-slate-500">{ticket.assignee?.name ?? '-'}</td>
                                        <td className="px-4 py-3 text-slate-500">{ticket.due_date ? fmtDate(ticket.due_date) : '-'}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                    <div className="grid gap-3 xl:hidden">
                        {recentTickets.slice(0, 5).map((ticket) => (
                            <Link key={ticket.id} href={route('admin.tickets.show', ticket.id)} className="block rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3 hover:bg-slate-100">
                                <div className="flex min-w-0 items-start justify-between gap-2">
                                    <p className="truncate text-sm font-semibold text-blue-700">{ticket.reference ?? `#${ticket.id}`}</p>
                                    <AppBadge tone={statusTone(String(ticket.status ?? ''))}>{ticket.status ?? '-'}</AppBadge>
                                </div>
                                <p className="mt-1 truncate text-sm font-medium text-slate-900">{ticket.title ?? '-'}</p>
                            </Link>
                        ))}
                    </div>
                    {recentTickets.length === 0 ? <EmptyState title="Sem pedidos recentes" /> : null}
                </AppCard>

                <AppCard>
                    <div className="mb-3 flex items-center justify-between">
                        <h2 className="text-lg font-semibold text-slate-950">Agenda de Hoje</h2>
                        <Link href={route('admin.events.index')} className="text-sm font-medium text-blue-600 hover:underline">Ver agenda</Link>
                    </div>
                    <div className="mt-2 space-y-3">
                        {operational.agenda_hoje.events.slice(0, 4).map((event) => (
                            <div key={`ev-${event.id}`} className="flex items-start gap-3 rounded-2xl border border-slate-100 bg-slate-50 px-3 py-3">
                                <div className="w-12 shrink-0 text-sm font-semibold text-slate-800">{fmtTime(event.start_at)}</div>
                                <div className="h-10 w-1 shrink-0 rounded-full bg-blue-500" />
                                <div className="min-w-0 flex-1"><p className="truncate text-sm font-semibold text-slate-900">{event.title ?? `Evento ${event.id}`}</p><p className="mt-1 text-xs text-slate-500">{event.location_text ?? event.status ?? '-'}</p></div>
                            </div>
                        ))}
                        {operational.agenda_hoje.reservations.slice(0, 2).map((r) => (
                            <div key={`res-${r.id}`} className="flex items-start gap-3 rounded-2xl border border-emerald-100 bg-emerald-50 px-3 py-3">
                                <div className="w-12 shrink-0 text-sm font-semibold text-emerald-800">{fmtTime(r.start_at)}</div>
                                <div className="h-10 w-1 shrink-0 rounded-full bg-emerald-500" />
                                <div className="min-w-0 flex-1"><p className="truncate text-sm font-semibold text-slate-900">{r.space?.name ?? r.purpose ?? `Reserva ${r.id}`}</p></div>
                            </div>
                        ))}
                        {operational.agenda_hoje.tasks.slice(0, 2).map((t) => (
                            <div key={`task-${t.id}`} className="flex items-start gap-3 rounded-2xl border border-amber-100 bg-amber-50 px-3 py-3">
                                <div className="w-12 shrink-0 text-sm font-semibold text-amber-800">Tarefa</div>
                                <div className="h-10 w-1 shrink-0 rounded-full bg-amber-500" />
                                <div className="min-w-0 flex-1"><p className="truncate text-sm font-semibold text-slate-900">{t.title ?? `Tarefa ${t.id}`}</p></div>
                            </div>
                        ))}
                        {operational.agenda_hoje.events.length === 0 && operational.agenda_hoje.reservations.length === 0 && operational.agenda_hoje.tasks.length === 0
                            ? <EmptyState title="Sem agenda hoje" /> : null}
                    </div>
                </AppCard>
            </div>

            {/* Row 2: Tarefas em Curso + Por Validar + Alertas */}
            <div className="mt-4 grid w-full gap-4 lg:grid-cols-3">
                <AppCard>
                    <div className="mb-3 flex items-center justify-between">
                        <h2 className="text-lg font-semibold text-slate-950">Tarefas em Curso</h2>
                        <Link href={route('admin.tasks.index')} className="text-sm font-medium text-blue-600 hover:underline">Ver todas</Link>
                    </div>
                    <div className="space-y-2">
                        {operational.tarefas_em_curso.map((task) => (
                            <div key={task.id} className={`rounded-2xl border px-4 py-3 ${task.is_overdue ? 'border-rose-200 bg-rose-50' : 'border-slate-100 bg-slate-50'}`}>
                                <div className="flex items-start justify-between gap-2">
                                    <p className="truncate text-sm font-semibold text-slate-900">{task.title ?? `Tarefa ${task.id}`}</p>
                                    <AppBadge tone={statusTone(String(task.status ?? ''))}>{task.status ?? '-'}</AppBadge>
                                </div>
                                <div className="mt-1 flex flex-wrap items-center gap-2">
                                    {task.is_overdue && <AppBadge tone="red">Atrasada</AppBadge>}
                                    {task.assignee?.name ? <span className="text-xs text-slate-500">{task.assignee.name}</span> : null}
                                    {task.ticket ? <Link href={route('admin.tickets.show', task.ticket.id)} className="text-xs text-blue-600 hover:underline">{task.ticket.reference ?? `#${task.ticket.id}`}</Link> : null}
                                </div>
                            </div>
                        ))}
                        {operational.tarefas_em_curso.length === 0 ? <EmptyState title="Sem tarefas em curso" /> : null}
                    </div>
                </AppCard>

                <AppCard>
                    <div className="mb-3 flex items-center justify-between">
                        <h2 className="text-lg font-semibold text-slate-950">Tarefas por Validar</h2>
                        <Link href={route('admin.tasks.index')} className="text-sm font-medium text-blue-600 hover:underline">Ver todas</Link>
                    </div>
                    <div className="space-y-2">
                        {operational.tarefas_por_validar.map((task) => (
                            <div key={task.id} className="rounded-2xl border border-violet-100 bg-violet-50 px-4 py-3">
                                <div className="flex items-start justify-between gap-2">
                                    <p className="truncate text-sm font-semibold text-slate-900">{task.title ?? `Tarefa ${task.id}`}</p>
                                    <AppBadge tone="amber">validação</AppBadge>
                                </div>
                                <div className="mt-1 flex flex-wrap items-center gap-2">
                                    {task.assignee?.name ? <span className="text-xs text-slate-500">{task.assignee.name}</span> : null}
                                    {task.ticket ? <Link href={route('admin.tickets.show', task.ticket.id)} className="text-xs text-blue-600 hover:underline">{task.ticket.reference ?? `#${task.ticket.id}`}</Link> : null}
                                </div>
                                <div className="mt-2">
                                    <Link href={route('admin.tasks.show', task.id)} className="rounded-md bg-violet-600 px-3 py-1 text-xs font-semibold text-white hover:bg-violet-700">Validar</Link>
                                </div>
                            </div>
                        ))}
                        {operational.tarefas_por_validar.length === 0 ? <EmptyState title="Sem tarefas para validar" /> : null}
                    </div>
                </AppCard>

                <AppCard>
                    <div className="mb-3 flex items-center justify-between">
                        <h2 className="text-lg font-semibold text-slate-950">Alertas</h2>
                        {totalAlerts > 0 && <AppBadge tone="red">{totalAlerts}</AppBadge>}
                    </div>
                    <div className="space-y-3">
                        {[
                            { label: 'Tarefas atrasadas', sub: 'Prazo ultrapassado', count: alertCounts.overdue_tasks, href: route('admin.tasks.index') },
                            { label: 'Tarefas reabertas', sub: 'Necessitam atenção', count: alertCounts.reopened_tasks, href: route('admin.tasks.index') },
                            { label: 'Por validar (tarefas)', sub: 'pending_validation', count: alertCounts.pending_validation_tasks, href: route('admin.tasks.index') },
                            { label: 'Pedidos aguarda validação', sub: 'aguarda_validacao', count: alertCounts.awaiting_validation_tickets, href: route('admin.tickets.index') },
                            { label: 'Reservas pendentes', sub: 'Aguardam aprovação', count: alertCounts.pending_reservations, href: route('admin.space-reservations.index') },
                        ].map((alert) => (
                            <Link key={alert.label} href={alert.href} className="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3 hover:bg-slate-100">
                                <div><p className="text-sm font-semibold text-slate-800">{alert.label}</p><p className="mt-0.5 text-xs text-slate-500">{alert.sub}</p></div>
                                <AppBadge tone={alert.count > 0 ? 'amber' : 'green'}>{alert.count}</AppBadge>
                            </Link>
                        ))}
                    </div>
                </AppCard>
            </div>

            {/* Row 3: Próximas Atividades + Espaços + RH */}
            <div className="mt-4 grid w-full gap-4 md:grid-cols-2 xl:grid-cols-3">
                <AppCard>
                    <div className="mb-3 flex items-center justify-between">
                        <h2 className="text-lg font-semibold text-slate-950">Próximas Atividades</h2>
                        <Link href={route('admin.operational-plans.index')} className="text-sm font-medium text-blue-600 hover:underline">Ver todas</Link>
                    </div>
                    <div className="space-y-3">
                        {operational.proximas_atividades.map((activity) => (
                            <div key={`${activity._type}-${activity.id}`} className="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                                <div className="flex items-start justify-between gap-2">
                                    <p className="text-sm font-semibold text-slate-900">{activity.title ?? `Atividade ${activity.id}`}</p>
                                    <AppBadge tone="blue">{typeBadge(activity._type)}</AppBadge>
                                </div>
                                <p className="mt-1 text-xs text-slate-500">{fmtDate(activity.start_at ?? activity.start_date)}</p>
                            </div>
                        ))}
                        {operational.proximas_atividades.length === 0 ? <EmptyState title="Sem atividades próximas" /> : null}
                    </div>
                </AppCard>

                <AppCard>
                    <div className="mb-3 flex items-center justify-between">
                        <h2 className="text-lg font-semibold text-slate-950">Espaços</h2>
                        <Link href={route('admin.space-reservations.index')} className="text-sm font-medium text-blue-600 hover:underline">Ver todos</Link>
                    </div>
                    <div className="mb-3 flex gap-4">
                        <div className="rounded-xl bg-emerald-50 px-4 py-2 text-center">
                            <p className="text-xl font-bold text-emerald-700">{operational.espacos.counts.today_total ?? 0}</p>
                            <p className="text-xs text-emerald-600">Hoje</p>
                        </div>
                        <div className="rounded-xl bg-amber-50 px-4 py-2 text-center">
                            <p className="text-xl font-bold text-amber-700">{operational.espacos.counts.pending_approval ?? 0}</p>
                            <p className="text-xs text-amber-600">Pendentes</p>
                        </div>
                    </div>
                    <div className="space-y-2">
                        {operational.espacos.today.slice(0, 4).map((r) => (
                            <div key={r.id} className="flex items-center justify-between rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                                <div>
                                    <p className="text-sm font-semibold text-slate-900">{r.space?.name ?? r.purpose ?? `Espaço ${r.id}`}</p>
                                    <p className="mt-0.5 text-xs text-slate-500">{fmtTime(r.start_at)} – {fmtTime(r.end_at)}</p>
                                </div>
                                <AppBadge tone={statusTone(String(r.status ?? ''))}>{r.status ?? '-'}</AppBadge>
                            </div>
                        ))}
                        {operational.espacos.today.length === 0 ? <EmptyState title="Sem reservas hoje" /> : null}
                    </div>
                </AppCard>

                <AppCard>
                    <div className="mb-3 flex items-center justify-between">
                        <h2 className="text-lg font-semibold text-slate-950">Recursos Humanos</h2>
                        <Link href={route('admin.hr.employees.index')} className="text-sm font-medium text-blue-600 hover:underline">Ver todos</Link>
                    </div>
                    <div className="space-y-3">
                        <div className="flex items-center justify-between rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                            <span className="text-sm text-slate-600">Presentes hoje</span>
                            <AppBadge tone="green">{operational.recursos_humanos.counts.present_today ?? 0}</AppBadge>
                        </div>
                        <div className="flex items-center justify-between rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                            <span className="text-sm text-slate-600">Ausentes hoje</span>
                            <AppBadge tone="amber">{operational.recursos_humanos.counts.absent_today ?? 0}</AppBadge>
                        </div>
                        {operational.recursos_humanos.task_summary.slice(0, 4).map((ts) => (
                            <div key={ts.assigned_to} className="flex items-center justify-between rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                                <span className="text-sm text-slate-700">{ts.assignee?.name ?? `Utilizador #${ts.assigned_to}`}</span>
                                <AppBadge tone="blue">{ts.task_count} tarefas</AppBadge>
                            </div>
                        ))}
                        {operational.recursos_humanos.absences.slice(0, 2).map((absence) => (
                            <div key={absence.id} className="rounded-2xl border border-slate-100 px-3 py-2 text-xs text-slate-500">
                                {String(absence.employee?.employee_number ?? `#${absence.id}`)} — {String(absence.status ?? '-')}
                            </div>
                        ))}
                    </div>
                </AppCard>
            </div>

            {/* Row 4: Planos Operacionais + Categorias */}
            <div className="mt-4 grid w-full gap-4 md:grid-cols-2">
                <AppCard>
                    <div className="mb-3 flex items-center justify-between">
                        <h2 className="text-lg font-semibold text-slate-950">Planos Operacionais</h2>
                        <Link href={route('admin.operational-plans.index')} className="text-sm font-medium text-blue-600 hover:underline">Ver todos</Link>
                    </div>
                    <div className="space-y-3">
                        {operational.planos_operacionais.map((plan) => (
                            <Link key={plan.id} href={route('admin.operational-plans.show', plan.id)} className="block rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3 hover:bg-slate-100">
                                <div className="flex items-start justify-between gap-2">
                                    <p className="truncate text-sm font-semibold text-slate-900">{plan.title ?? `Plano ${plan.id}`}</p>
                                    <AppBadge tone={statusTone(String(plan.status ?? ''))}>{plan.status ?? '-'}</AppBadge>
                                </div>
                                <div className="mt-2">
                                    <div className="flex items-center justify-between text-xs text-slate-500"><span>Progresso</span><span>{plan.progress_percent ?? 0}%</span></div>
                                    <div className="mt-1 h-1.5 rounded-full bg-slate-200"><div className="h-1.5 rounded-full bg-blue-500" style={{ width: `${plan.progress_percent ?? 0}%` }} /></div>
                                </div>
                                {plan.owner?.name ? <p className="mt-1 text-xs text-slate-400">Responsável: {plan.owner.name}</p> : null}
                            </Link>
                        ))}
                        {operational.planos_operacionais.length === 0 ? <EmptyState title="Sem planos operacionais ativos" /> : null}
                    </div>
                </AppCard>

                <AppCard>
                    <h2 className="text-lg font-semibold text-slate-950">Pedidos por Categoria</h2>
                    <div className="mt-4 space-y-3">
                        {Object.entries(data.ticket_category_breakdown).slice(0, 6).map(([category, total]) => {
                            const openTickets = Number(resumo.open_tickets ?? 1);
                            const ratio = openTickets > 0 ? Math.min((Number(total) / openTickets) * 100, 100) : 0;
                            return (
                                <div key={category}>
                                    <div className="flex items-center justify-between text-sm"><span className="text-slate-700">{category}</span><span className="font-semibold text-slate-900">{total}</span></div>
                                    <div className="mt-1 h-2 rounded-full bg-slate-100"><div className="h-2 rounded-full bg-blue-500" style={{ width: `${ratio}%` }} /></div>
                                </div>
                            );
                        })}
                        {Object.keys(data.ticket_category_breakdown).length === 0 ? <EmptyState title="Sem categorias" /> : null}
                    </div>
                </AppCard>
            </div>

            {lowStockItems.length > 0 ? (
                <AppCard className="mt-4 lg:hidden">
                    <h2 className="text-lg font-semibold text-slate-950">Alertas de stock</h2>
                    <div className="mt-3 space-y-2">
                        {lowStockItems.slice(0, 4).map((item) => (
                            <div key={item.id} className="rounded-2xl border border-slate-100 bg-slate-50 px-3 py-2 text-sm text-slate-700">
                                {item.name ?? `Item ${item.id}`} ({String(item.current_stock ?? 0)}/{String(item.minimum_stock ?? 0)})
                            </div>
                        ))}
                    </div>
                </AppCard>
            ) : null}
        </AdminLayout>
    );
}
