import OperationalPlanFilters from '@/Components/OperationalPlanFilters';
import OperationalPlanProgressBar from '@/Components/OperationalPlanProgressBar';
import OperationalPlanStatusBadge from '@/Components/OperationalPlanStatusBadge';
import OperationalPlanTabs from '@/Components/OperationalPlanTabs';
import OperationalPlanTypeBadge from '@/Components/OperationalPlanTypeBadge';
import OperationalPlanVisibilityBadge from '@/Components/OperationalPlanVisibilityBadge';
import PrimaryButton from '@/Components/PrimaryButton';
import AdminLayout from '@/Layouts/AdminLayout';
import { Link, router } from '@inertiajs/react';

type Props = {
    plans: { data: any[] };
    filters: Record<string, string | null | undefined>;
    statuses: string[];
    types: string[];
    visibilities: string[];
    departments: { id: number; name: string }[];
    teams: { id: number; name: string }[];
};

export default function OperationalPlansIndex({ plans, filters, statuses, types, visibilities, departments, teams }: Props) {
    const onFilterChange = (name: string, value: string) => {
        router.get(route('admin.operational-plans.index'), { ...filters, [name]: value || undefined }, { preserveState: true, replace: true });
    };

    return (
        <AdminLayout title="Planeamento Operacional" subtitle="Gestão de planos, atividades e execução" headerActions={<OperationalPlanTabs active="plans" />}>
            <div className="mb-4 flex items-center justify-between">
                <h2 className="text-xl font-semibold text-slate-900">Planos Operacionais</h2>
                <Link href={route('admin.operational-plans.create')}><PrimaryButton>Criar Plano</PrimaryButton></Link>
            </div>

            <OperationalPlanFilters filters={filters} statuses={statuses} types={types} visibilities={visibilities} departments={departments} teams={teams} onChange={onFilterChange} />

            <div className="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white">
                <table className="min-w-full text-sm">
                    <thead className="bg-slate-50 text-left text-slate-600">
                        <tr>
                            <th className="px-4 py-3">Título</th>
                            <th className="px-4 py-3">Tipo</th>
                            <th className="px-4 py-3">Estado</th>
                            <th className="px-4 py-3">Visibilidade</th>
                            <th className="px-4 py-3">Período</th>
                            <th className="px-4 py-3">Responsável</th>
                            <th className="px-4 py-3">Progresso</th>
                            <th className="px-4 py-3">Orçamento</th>
                        </tr>
                    </thead>
                    <tbody>
                        {plans.data.map((plan) => (
                            <tr key={plan.id} className="border-t border-slate-200">
                                <td className="px-4 py-3"><Link href={route('admin.operational-plans.show', plan.id)} className="font-medium text-slate-900 hover:underline">{plan.title}</Link></td>
                                <td className="px-4 py-3"><OperationalPlanTypeBadge type={plan.plan_type} /></td>
                                <td className="px-4 py-3"><OperationalPlanStatusBadge status={plan.status} /></td>
                                <td className="px-4 py-3"><OperationalPlanVisibilityBadge visibility={plan.visibility} /></td>
                                <td className="px-4 py-3 text-slate-600">{plan.start_date}{plan.end_date ? ` → ${plan.end_date}` : ''}</td>
                                <td className="px-4 py-3 text-slate-600">{plan.owner?.name ?? '-'}</td>
                                <td className="px-4 py-3"><OperationalPlanProgressBar value={plan.progress_percent ?? 0} /></td>
                                <td className="px-4 py-3 text-slate-600">{plan.budget_estimate ?? '-'}</td>
                            </tr>
                        ))}
                        {plans.data.length === 0 ? <tr><td colSpan={8} className="px-4 py-6 text-center text-slate-500">Sem planos para mostrar.</td></tr> : null}
                    </tbody>
                </table>
            </div>
        </AdminLayout>
    );
}
