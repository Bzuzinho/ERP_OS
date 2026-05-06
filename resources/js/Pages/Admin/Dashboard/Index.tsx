import AppBadge from '@/Components/App/AppBadge';
import AppCard from '@/Components/App/AppCard';
import EmptyState from '@/Components/App/EmptyState';
import AdminLayout from '@/Layouts/AdminLayout';
import { Link } from '@inertiajs/react';

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
    const recentTickets = data.recent_tickets as Array<{ id: number; reference?: string; title?: string; category?: string; status?: string; priority?: string; assignee?: { name?: string } | null; due_date?: string | null }>;
    const todayEvents = data.today_events as Array<{ id: number; title?: string; start_at?: string; location_text?: string; status?: string }>;
    const reservations = data.today_reservations as Array<{ id: number; purpose?: string; space?: { name?: string } | null; status?: string }>;
    const upcomingActivities = data.upcoming_public_activities as Array<{ id: number; title?: string; start_date?: string; location_text?: string }>;
    const lowStockItems = data.low_stock_items as Array<{ id: number; name?: string; minimum_stock?: number; current_stock?: number }>;

    const statusTone = (status: string): 'blue' | 'amber' | 'green' | 'red' | 'slate' => {
        const normalized = status.toLowerCase();
        if (normalized.includes('resol') || normalized.includes('fech')) return 'green';
        if (normalized.includes('urgent') || normalized.includes('alta')) return 'red';
        if (normalized.includes('pend') || normalized.includes('anal')) return 'amber';
        if (normalized.includes('novo') || normalized.includes('exec') || normalized.includes('abert')) return 'blue';
        return 'slate';
    };

    const kpis = [
        {
            label: 'Pedidos em aberto',
            value: String(data.kpis.open_tickets ?? 0),
            trend: `${String(data.kpis.overdue_tickets ?? 0)} com prazo crítico`,
            tone: 'blue',
            href: route('admin.tickets.index'),
            icon: (
                <svg viewBox="0 0 24 24" className="h-5 w-5" fill="none" stroke="currentColor" strokeWidth="1.8" aria-hidden="true">
                    <path d="M4 8.5a2.5 2.5 0 012.5-2.5h11A2.5 2.5 0 0120 8.5v2a2 2 0 000 4v2a2.5 2.5 0 01-2.5 2.5h-11A2.5 2.5 0 014 16.5v-2a2 2 0 000-4z" />
                </svg>
            ),
        },
        {
            label: 'Urgentes',
            value: String(data.kpis.urgent_tickets ?? 0),
            trend: 'Prioridade alta',
            tone: 'red',
            href: route('admin.tickets.index'),
            icon: (
                <svg viewBox="0 0 24 24" className="h-5 w-5" fill="none" stroke="currentColor" strokeWidth="1.8" aria-hidden="true">
                    <path d="M12 9v4" />
                    <path d="M12 17h.01" />
                    <path d="M10.3 3.6L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.6a2 2 0 00-3.4 0z" />
                </svg>
            ),
        },
        {
            label: 'Reservas hoje',
            value: String(data.kpis.reservations_today ?? 0),
            trend: `${String(data.kpis.pending_reservations ?? 0)} pendentes`,
            tone: 'green',
            href: route('admin.space-reservations.index'),
            icon: (
                <svg viewBox="0 0 24 24" className="h-5 w-5" fill="none" stroke="currentColor" strokeWidth="1.8" aria-hidden="true">
                    <rect x="3" y="5" width="18" height="16" rx="2" />
                    <path d="M8 3v4" />
                    <path d="M16 3v4" />
                    <path d="M3 10h18" />
                </svg>
            ),
        },
        {
            label: 'Stock baixo',
            value: String(data.kpis.low_stock_items ?? 0),
            trend: 'Itens para reposição',
            tone: 'amber',
            href: route('admin.inventory-items.index'),
            icon: (
                <svg viewBox="0 0 24 24" className="h-5 w-5" fill="none" stroke="currentColor" strokeWidth="1.8" aria-hidden="true">
                    <path d="M21 16V8" />
                    <path d="M3 16V8" />
                    <path d="M12 21V9" />
                    <path d="M12 9L3.5 4.5" />
                    <path d="M12 9l8.5-4.5" />
                </svg>
            ),
        },
        {
            label: 'Ausências',
            value: String(data.kpis.absences_today ?? 0),
            trend: `${String(data.kpis.present_employees_today ?? 0)} presentes`,
            tone: 'indigo',
            href: route('admin.hr.attendance.index'),
            icon: (
                <svg viewBox="0 0 24 24" className="h-5 w-5" fill="none" stroke="currentColor" strokeWidth="1.8" aria-hidden="true">
                    <circle cx="12" cy="8" r="3" />
                    <path d="M6 19a6 6 0 0112 0" />
                </svg>
            ),
        },
        {
            label: 'Atividades planeadas',
            value: String(data.kpis.plans_in_execution ?? 0),
            trend: `${String(data.kpis.plans_pending_approval ?? 0)} em aprovação`,
            tone: 'blue',
            href: route('admin.operational-plans.index'),
            icon: (
                <svg viewBox="0 0 24 24" className="h-5 w-5" fill="none" stroke="currentColor" strokeWidth="1.8" aria-hidden="true">
                    <path d="M4 20h16" />
                    <path d="M6 16l4-4 3 3 5-6" />
                </svg>
            ),
        },
    ];

    const toneStyles: Record<string, string> = {
        blue: 'bg-blue-50 text-blue-700',
        red: 'bg-rose-50 text-rose-700',
        green: 'bg-emerald-50 text-emerald-700',
        amber: 'bg-amber-50 text-amber-700',
        indigo: 'bg-indigo-50 text-indigo-700',
    };

    return (
        <AdminLayout title="Dashboard Operacional" subtitle="Visão geral da operação da junta">
            <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                {kpis.map((kpi) => (
                    <Link key={kpi.label} href={kpi.href} className="group block focus-visible:outline-none">
                        <AppCard className="rounded-2xl p-4 transition-transform duration-150 group-hover:-translate-y-0.5 group-hover:shadow-md group-focus-visible:ring-2 group-focus-visible:ring-blue-500 group-focus-visible:ring-offset-2">
                            <div className="flex items-start justify-between gap-2">
                                <div className={`inline-flex h-9 w-9 items-center justify-center rounded-full ${toneStyles[kpi.tone]}`}>{kpi.icon}</div>
                                <span className="text-[11px] text-slate-400">Hoje</span>
                            </div>
                            <p className="mt-3 text-[13px] font-medium text-slate-600">{kpi.label}</p>
                            <p className="mt-1 text-2xl font-bold text-slate-950 sm:text-3xl">{kpi.value}</p>
                            <p className="mt-2 text-xs text-slate-500">{kpi.trend}</p>
                        </AppCard>
                    </Link>
                ))}
            </div>

            <div className="mt-4 grid w-full gap-4 lg:grid-cols-3">
                <AppCard className="lg:col-span-2">
                    <div className="mb-3 flex items-center justify-between">
                        <h2 className="text-lg font-semibold text-slate-950">Pedidos Recentes</h2>
                        <Link href={route('admin.tickets.index')} className="text-sm font-medium text-blue-600 hover:text-blue-700 hover:underline">
                            Ver todos
                        </Link>
                    </div>

                    <div className="hidden overflow-x-auto rounded-2xl border border-slate-200 xl:block">
                        <table className="min-w-full text-sm">
                            <thead className="bg-slate-50 text-left text-slate-500">
                                <tr>
                                    <th className="px-4 py-3">Ref.</th>
                                    <th className="px-4 py-3">Assunto</th>
                                    <th className="px-4 py-3">Categoria</th>
                                    <th className="px-4 py-3">Estado</th>
                                    <th className="px-4 py-3">Prioridade</th>
                                    <th className="px-4 py-3">Responsável</th>
                                    <th className="px-4 py-3">Prazo</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {recentTickets.slice(0, 7).map((ticket) => (
                                    <tr key={ticket.id}>
                                        <td className="px-4 py-3 font-semibold text-blue-700">{ticket.reference ?? `#${ticket.id}`}</td>
                                        <td className="px-4 py-3 text-slate-800">{ticket.title ?? '-'}</td>
                                        <td className="px-4 py-3 text-slate-500">{ticket.category ?? '-'}</td>
                                        <td className="px-4 py-3"><AppBadge tone={statusTone(String(ticket.status ?? ''))}>{ticket.status ?? '-'}</AppBadge></td>
                                        <td className="px-4 py-3"><AppBadge tone={statusTone(String(ticket.priority ?? ''))}>{ticket.priority ?? '-'}</AppBadge></td>
                                        <td className="px-4 py-3 text-slate-500">{ticket.assignee?.name ?? '-'}</td>
                                        <td className="px-4 py-3 text-slate-500">{ticket.due_date ? new Date(ticket.due_date).toLocaleDateString() : '-'}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    <div className="grid gap-3 xl:hidden">
                        {recentTickets.slice(0, 5).map((ticket) => (
                            <div key={ticket.id} className="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                                <div className="flex min-w-0 items-start justify-between gap-2">
                                    <p className="truncate text-sm font-semibold text-blue-700">{ticket.reference ?? `#${ticket.id}`}</p>
                                    <AppBadge tone={statusTone(String(ticket.status ?? ''))}>{ticket.status ?? '-'}</AppBadge>
                                </div>
                                <p className="mt-1 truncate text-sm font-medium text-slate-900">{ticket.title ?? '-'}</p>
                                <div className="mt-2 flex min-w-0 flex-wrap items-center gap-2">
                                    <AppBadge tone={statusTone(String(ticket.priority ?? ''))}>{ticket.priority ?? '-'}</AppBadge>
                                    {ticket.assignee?.name ? <span className="truncate text-xs text-slate-500">{ticket.assignee.name}</span> : null}
                                    {ticket.due_date ? <span className="text-xs text-slate-400">{new Date(ticket.due_date).toLocaleDateString()}</span> : null}
                                </div>
                            </div>
                        ))}
                    </div>

                    {recentTickets.length === 0 ? <EmptyState title="Sem pedidos recentes" /> : null}
                </AppCard>

                <AppCard>
                    <div className="mb-3 flex items-center justify-between">
                        <h2 className="text-lg font-semibold text-slate-950">Agenda de Hoje</h2>
                        <Link href={route('admin.events.index')} className="text-sm font-medium text-blue-600 hover:text-blue-700 hover:underline">
                            Ver agenda
                        </Link>
                    </div>
                    <div className="mt-4 space-y-3">
                        {todayEvents.slice(0, 5).map((event) => (
                            <div key={event.id} className="flex items-start gap-3 rounded-2xl border border-slate-100 bg-slate-50 px-3 py-3">
                                <div className="w-12 text-sm font-semibold text-slate-800">
                                    {event.start_at ? new Date(event.start_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) : '--:--'}
                                </div>
                                <div className="h-10 w-1 rounded-full bg-blue-500" />
                                <div className="min-w-0 flex-1">
                                    <p className="truncate text-sm font-semibold text-slate-900">{event.title ?? `Evento ${event.id}`}</p>
                                    <p className="mt-1 text-xs text-slate-500">{event.location_text ?? event.status ?? '-'}</p>
                                </div>
                            </div>
                        ))}
                        {todayEvents.length === 0 ? <EmptyState title="Sem eventos hoje" /> : null}
                    </div>
                </AppCard>
            </div>

            <div className="mt-4 grid w-full gap-4 md:grid-cols-2 xl:grid-cols-3">
                <AppCard>
                    <div className="mb-3 flex items-center justify-between">
                        <h2 className="text-lg font-semibold text-slate-950">Próximas Atividades</h2>
                        <Link href={route('admin.operational-plans.index')} className="text-sm font-medium text-blue-600 hover:text-blue-700 hover:underline">
                            Ver todas
                        </Link>
                    </div>
                    <div className="mt-4 space-y-3">
                        {upcomingActivities.slice(0, 4).map((activity) => (
                            <div key={activity.id} className="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                                <p className="text-sm font-semibold text-slate-900">{activity.title ?? `Atividade ${activity.id}`}</p>
                                <p className="mt-1 text-xs text-slate-500">{activity.start_date ?? '-'} {activity.location_text ? `• ${activity.location_text}` : ''}</p>
                            </div>
                        ))}
                        {upcomingActivities.length === 0 ? <EmptyState title="Sem atividades" /> : null}
                    </div>
                </AppCard>

                <AppCard>
                    <div className="mb-3 flex items-center justify-between">
                        <h2 className="text-lg font-semibold text-slate-950">Alertas</h2>
                        <Link href={route('admin.inventory-items.index')} className="text-sm font-medium text-blue-600 hover:text-blue-700 hover:underline">
                            Ver todos
                        </Link>
                    </div>
                    <div className="space-y-3">
                        <div className="rounded-2xl bg-slate-50 px-4 py-3">
                            <div className="flex items-center justify-between gap-2">
                                <p className="text-sm font-semibold text-slate-800">Artigos com stock baixo</p>
                                <AppBadge tone="amber">{String(data.kpis.low_stock_items ?? 0)}</AppBadge>
                            </div>
                            <p className="mt-1 text-xs text-slate-500">Verifique os materiais em falta</p>
                        </div>
                        <div className="rounded-2xl bg-slate-50 px-4 py-3">
                            <div className="flex items-center justify-between gap-2">
                                <p className="text-sm font-semibold text-slate-800">Pedidos urgentes</p>
                                <AppBadge tone="red">{String(data.kpis.urgent_tickets ?? 0)}</AppBadge>
                            </div>
                            <p className="mt-1 text-xs text-slate-500">Alguns pedidos ultrapassaram o prazo</p>
                        </div>
                        <div className="rounded-2xl bg-slate-50 px-4 py-3">
                            <div className="flex items-center justify-between gap-2">
                                <p className="text-sm font-semibold text-slate-800">Pedidos por analisar</p>
                                <AppBadge tone="blue">{String(data.kpis.open_tickets ?? 0)}</AppBadge>
                            </div>
                            <p className="mt-1 text-xs text-slate-500">Necessitam validação inicial</p>
                        </div>
                    </div>
                </AppCard>

                <AppCard>
                    <h2 className="text-lg font-semibold text-slate-950">Pedidos por Categoria</h2>
                    <div className="mt-4 space-y-3">
                        {Object.entries(data.ticket_category_breakdown)
                            .slice(0, 6)
                            .map(([category, total]) => {
                                const ratio = Number(data.kpis.open_tickets ?? 1) > 0 ? Math.min((Number(total) / Number(data.kpis.open_tickets ?? 1)) * 100, 100) : 0;

                                return (
                                    <div key={category}>
                                        <div className="flex items-center justify-between text-sm">
                                            <span className="text-slate-700">{category}</span>
                                            <span className="font-semibold text-slate-900">{total}</span>
                                        </div>
                                        <div className="mt-1 h-2 rounded-full bg-slate-100">
                                            <div className="h-2 rounded-full bg-blue-500" style={{ width: `${ratio}%` }} />
                                        </div>
                                    </div>
                                );
                            })}
                        {Object.keys(data.ticket_category_breakdown).length === 0 ? <EmptyState title="Sem categorias" /> : null}
                    </div>
                </AppCard>

                <AppCard>
                    <div className="mb-3 flex items-center justify-between">
                        <h2 className="text-lg font-semibold text-slate-950">Espaços</h2>
                        <Link href={route('admin.space-reservations.index')} className="text-sm font-medium text-blue-600 hover:text-blue-700 hover:underline">
                            Ver todos
                        </Link>
                    </div>
                    <div className="space-y-3">
                        {reservations.slice(0, 4).map((reservation) => (
                            <div key={reservation.id} className="flex items-center justify-between rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                                <div>
                                    <p className="text-sm font-semibold text-slate-900">{reservation.space?.name ?? reservation.purpose ?? `Espaço ${reservation.id}`}</p>
                                    <p className="mt-1 text-xs text-slate-500">{reservation.purpose ?? '-'}</p>
                                </div>
                                <AppBadge tone={statusTone(String(reservation.status ?? ''))}>{reservation.status ?? 'Ativo'}</AppBadge>
                            </div>
                        ))}
                        {reservations.length === 0 ? <EmptyState title="Sem reservas hoje" /> : null}
                    </div>
                </AppCard>

                <AppCard>
                    <div className="mb-3 flex items-center justify-between">
                        <h2 className="text-lg font-semibold text-slate-950">Recursos Humanos</h2>
                        <Link href={route('admin.hr.employees.index')} className="text-sm font-medium text-blue-600 hover:text-blue-700 hover:underline">
                            Ver todos
                        </Link>
                    </div>
                    <div className="space-y-3">
                        <div className="flex items-center justify-between rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                            <span className="text-sm text-slate-600">Total de colaboradores</span>
                            <span className="text-lg font-bold text-slate-900">{Number(data.kpis.present_employees_today ?? 0) + Number(data.kpis.absences_today ?? 0)}</span>
                        </div>
                        <div className="flex items-center justify-between rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                            <span className="text-sm text-slate-600">Presentes</span>
                            <AppBadge tone="green">{String(data.kpis.present_employees_today ?? 0)}</AppBadge>
                        </div>
                        <div className="flex items-center justify-between rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                            <span className="text-sm text-slate-600">Ausentes</span>
                            <AppBadge tone="amber">{String(data.kpis.absences_today ?? 0)}</AppBadge>
                        </div>
                        {todayAbsences.slice(0, 2).map((absence) => (
                            <div key={absence.id} className="rounded-2xl border border-slate-100 px-3 py-2 text-xs text-slate-500">
                                {String(absence.employee?.employee_number ?? `#${absence.id}`)} - {String(absence.status ?? '-')}
                            </div>
                        ))}
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
