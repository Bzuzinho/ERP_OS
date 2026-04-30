import AdminLayout from '@/Layouts/AdminLayout';

type Stat = {
    label: string;
    value: number;
};

type AdminDashboardProps = {
    stats: Stat[];
    pendingTasks: { id: number; title: string; status: string; assignee?: { id: number; name: string } | null }[];
    todayEvents: { id: number; title: string; start_at: string; end_at: string; status: string }[];
};

export default function AdminDashboard({
    stats,
    pendingTasks,
    todayEvents,
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
            </div>
        </AdminLayout>
    );
}