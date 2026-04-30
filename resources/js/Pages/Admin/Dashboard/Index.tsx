import AdminLayout from '@/Layouts/AdminLayout';

type Stat = {
    label: string;
    value: number;
};

type Section = {
    title: string;
    description: string;
};

type AdminDashboardProps = {
    stats: Stat[];
    sections: Section[];
};

export default function AdminDashboard({
    stats,
    sections,
}: AdminDashboardProps) {
    return (
        <AdminLayout
            title="Dashboard Operacional"
            subtitle="Visão geral da operação da junta"
            headerActions={
                <div className="rounded-3xl bg-gradient-to-r from-slate-900 via-slate-800 to-emerald-800 px-6 py-5 text-white shadow-lg">
                    <p className="text-sm uppercase tracking-[0.22em] text-emerald-200">
                        Sprint 0
                    </p>
                    <p className="mt-2 max-w-3xl text-sm text-slate-200">
                        Fundação técnica preparada para tickets, agenda, espaços, inventário, RH e planeamento entrarem nas próximas sprints.
                    </p>
                </div>
            }
        >
            <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                {stats.map((stat) => (
                    <section key={stat.label} className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p className="text-sm text-slate-500">{stat.label}</p>
                        <p className="mt-4 text-4xl font-semibold text-slate-950">{stat.value}</p>
                    </section>
                ))}
            </div>

            <div className="mt-8 grid gap-4 xl:grid-cols-3">
                {sections.map((section) => (
                    <section key={section.title} className="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                        <p className="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-700">
                            Placeholder
                        </p>
                        <h2 className="mt-3 text-xl font-semibold text-slate-950">{section.title}</h2>
                        <p className="mt-3 text-sm leading-6 text-slate-600">{section.description}</p>
                    </section>
                ))}
            </div>
        </AdminLayout>
    );
}