import AdminLayout from '@/Layouts/AdminLayout';

type Stat = {
    label: string;
    value: number;
};

type AdminDashboardProps = {
    stats: Stat[];
    pendingTasks: { id: number; title: string; status: string; assignee?: { id: number; name: string } | null }[];
    todayEvents: { id: number; title: string; start_at: string; end_at: string; status: string }[];
    todayReservations: { id: number; purpose: string; start_at: string; status: string; space?: { id: number; name: string } | null; contact?: { id: number; name: string } | null }[];
    pendingMaintenance: { id: number; title: string; status: string; space?: { id: number; name: string } | null; assignee?: { id: number; name: string } | null }[];
    lowStockItems: { id: number; name: string; sku: string | null; current_stock: number; minimum_stock: number | null }[];
    overdueLoans: { id: number; quantity: number; expected_return_at: string | null; item?: { id: number; name: string; sku: string | null } | null; borrowerUser?: { id: number; name: string } | null; borrowerContact?: { id: number; name: string } | null }[];
};

export default function AdminDashboard({
    stats,
    pendingTasks,
    todayEvents,
    todayReservations,
    pendingMaintenance,
    lowStockItems,
    overdueLoans,
}: AdminDashboardProps) {
    return (
        <AdminLayout
            title="Dashboard Operacional"
            subtitle="Visão geral da operação da junta"
            headerActions={
                <div className="rounded-3xl bg-gradient-to-r from-slate-900 via-slate-800 to-emerald-800 px-6 py-5 text-white shadow-lg">
                    <p className="text-sm uppercase tracking-[0.22em] text-emerald-200">
                        Sprint 2
                    </p>
                    <p className="mt-2 max-w-3xl text-sm text-slate-200">
                        Painel operacional com tarefas internas e agenda ligados aos modulos de tickets e contactos.
                    </p>
                </div>
            }
        >
            <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                {stats.map((stat) => (
                    <section key={stat.label} className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p className="text-sm text-slate-500">{stat.label}</p>
                        <p className="mt-4 text-4xl font-semibold text-slate-950">{stat.value}</p>
                    </section>
                ))}
            </div>

            <div className="mt-8 grid gap-4 xl:grid-cols-2">
                <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p className="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Operacao</p>
                    <h2 className="mt-3 text-xl font-semibold text-slate-950">Tarefas Pendentes</h2>
                    <ul className="mt-4 space-y-2 text-sm">
                        {pendingTasks.map((task) => (
                            <li key={task.id} className="rounded-xl bg-slate-50 px-3 py-2 text-slate-700">
                                {task.title} • {task.status} • {task.assignee?.name ?? 'Sem responsavel'}
                            </li>
                        ))}
                        {pendingTasks.length === 0 ? <li className="text-slate-500">Sem tarefas pendentes.</li> : null}
                    </ul>
                </section>

                <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p className="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Reservas</p>
                    <h2 className="mt-3 text-xl font-semibold text-slate-950">Reservas de Hoje</h2>
                    <ul className="mt-4 space-y-2 text-sm">
                        {todayReservations.map((reservation) => (
                            <li key={reservation.id} className="rounded-xl bg-slate-50 px-3 py-2 text-slate-700">
                                {reservation.space?.name ?? '-'} • {reservation.purpose} • {new Date(reservation.start_at).toLocaleTimeString()} • {reservation.status}
                            </li>
                        ))}
                        {todayReservations.length === 0 ? <li className="text-slate-500">Sem reservas hoje.</li> : null}
                    </ul>
                </section>
            </div>

            <div className="mt-4 grid gap-4 xl:grid-cols-2">
                <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p className="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Agenda</p>
                    <h2 className="mt-3 text-xl font-semibold text-slate-950">Agenda de Hoje</h2>
                    <ul className="mt-4 space-y-2 text-sm">
                        {todayEvents.map((event) => (
                            <li key={event.id} className="rounded-xl bg-slate-50 px-3 py-2 text-slate-700">
                                {event.title} • {new Date(event.start_at).toLocaleTimeString()} - {new Date(event.end_at).toLocaleTimeString()} • {event.status}
                            </li>
                        ))}
                        {todayEvents.length === 0 ? <li className="text-slate-500">Sem eventos hoje.</li> : null}
                    </ul>
                </section>

                <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p className="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">Manutencao</p>
                    <h2 className="mt-3 text-xl font-semibold text-slate-950">Manutencoes Pendentes</h2>
                    <ul className="mt-4 space-y-2 text-sm">
                        {pendingMaintenance.map((maintenance) => (
                            <li key={maintenance.id} className="rounded-xl bg-slate-50 px-3 py-2 text-slate-700">
                                {maintenance.space?.name ?? '-'} • {maintenance.title} • {maintenance.status}
                            </li>
                        ))}
                        {pendingMaintenance.length === 0 ? <li className="text-slate-500">Sem manutencoes pendentes.</li> : null}
                    </ul>
                </section>

                <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p className="text-xs font-semibold uppercase tracking-[0.2em] text-amber-700">Stock Baixo</p>
                    <h2 className="mt-3 text-xl font-semibold text-slate-950">Itens Criticos</h2>
                    <ul className="mt-4 space-y-2 text-sm">
                        {lowStockItems.map((item) => (
                            <li key={item.id} className="rounded-xl bg-amber-50 px-3 py-2 text-amber-900">
                                {item.name} ({item.sku ?? 'sem sku'}) • {item.current_stock} / min {item.minimum_stock ?? '-'}
                            </li>
                        ))}
                        {lowStockItems.length === 0 ? <li className="text-slate-500">Sem itens com stock baixo.</li> : null}
                    </ul>
                </section>
            </div>

            <div className="mt-4 grid gap-4 xl:grid-cols-1">
                <section className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p className="text-xs font-semibold uppercase tracking-[0.2em] text-rose-700">Emprestimos</p>
                    <h2 className="mt-3 text-xl font-semibold text-slate-950">Emprestimos em Atraso</h2>
                    <ul className="mt-4 space-y-2 text-sm">
                        {overdueLoans.map((loan) => (
                            <li key={loan.id} className="rounded-xl bg-rose-50 px-3 py-2 text-rose-900">
                                {loan.item?.name ?? '-'} • {loan.borrowerUser?.name ?? loan.borrowerContact?.name ?? '-'} • {loan.quantity} • devolucao prevista {loan.expected_return_at ? new Date(loan.expected_return_at).toLocaleDateString() : '-'}
                            </li>
                        ))}
                        {overdueLoans.length === 0 ? <li className="text-slate-500">Sem emprestimos em atraso.</li> : null}
                    </ul>
                </section>
            </div>
        </AdminLayout>
    );
}