import PortalLayout from '@/Layouts/PortalLayout';

type Stat = {
    label: string;
    value: number;
};

type PortalDashboardProps = {
    stats: Stat[];
    actions: string[];
    upcomingEvents: { id: number; title: string; start_at: string; end_at: string; status: string }[];
};

export default function PortalDashboard({
    stats,
    actions,
    upcomingEvents,
}: PortalDashboardProps) {
    return (
        <PortalLayout
            title="O meu portal"
            subtitle="Acompanhe os seus pedidos, reservas e documentos"
            headerActions={
                <div className="rounded-3xl border border-amber-200 bg-gradient-to-r from-amber-500 to-orange-500 px-6 py-5 text-white shadow-lg">
                    <p className="text-sm uppercase tracking-[0.22em] text-amber-100">
                        Sprint 2
                    </p>
                    <p className="mt-2 max-w-2xl text-sm text-amber-50">
                        Portal com visao de pedidos ativos e agenda futura associada aos seus contactos e participacoes.
                    </p>
                </div>
            }
        >
            <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                {stats.map((stat) => (
                    <section key={stat.label} className="rounded-3xl border border-stone-200 bg-white p-6 shadow-sm">
                        <p className="text-sm text-stone-500">{stat.label}</p>
                        <p className="mt-4 text-4xl font-semibold text-stone-950">{stat.value}</p>
                    </section>
                ))}
            </div>

            <div className="mt-8 rounded-3xl border border-stone-200 bg-white p-6 shadow-sm">
                <p className="text-xs font-semibold uppercase tracking-[0.2em] text-amber-700">
                    Ações rápidas
                </p>
                <div className="mt-4 flex flex-wrap gap-3">
                    {actions.map((action) => (
                        <button
                            key={action}
                            type="button"
                            className="rounded-full bg-stone-900 px-5 py-3 text-sm font-medium text-white transition hover:bg-stone-700"
                        >
                            {action}
                        </button>
                    ))}
                </div>
            </div>

            <div className="mt-8 grid gap-4 lg:grid-cols-2">
                <section className="rounded-3xl border border-stone-200 bg-white p-6 shadow-sm">
                    <p className="text-xs font-semibold uppercase tracking-[0.2em] text-amber-700">
                        Pedidos
                    </p>
                    <h2 className="mt-3 text-xl font-semibold text-stone-950">Estado dos pedidos</h2>
                    <p className="mt-3 text-sm leading-6 text-stone-600">
                        Placeholder para o acompanhamento de pedidos submetidos e respetivas respostas da junta.
                    </p>
                </section>
                <section className="rounded-3xl border border-stone-200 bg-white p-6 shadow-sm">
                    <p className="text-xs font-semibold uppercase tracking-[0.2em] text-amber-700">
                        Agenda
                    </p>
                    <h2 className="mt-3 text-xl font-semibold text-stone-950">Proximas marcacoes</h2>
                    <ul className="mt-3 space-y-2 text-sm text-stone-700">
                        {upcomingEvents.map((event) => (
                            <li key={event.id} className="rounded-xl bg-stone-50 px-3 py-2">
                                {event.title} • {new Date(event.start_at).toLocaleString()} - {new Date(event.end_at).toLocaleString()} • {event.status}
                            </li>
                        ))}
                        {upcomingEvents.length === 0 ? <li className="text-stone-500">Sem marcacoes futuras.</li> : null}
                    </ul>
                </section>
            </div>
        </PortalLayout>
    );
}