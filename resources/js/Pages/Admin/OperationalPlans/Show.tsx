import OperationalPlanParticipantsList from '@/Components/OperationalPlanParticipantsList';
import OperationalPlanProgressBar from '@/Components/OperationalPlanProgressBar';
import OperationalPlanResourcesList from '@/Components/OperationalPlanResourcesList';
import OperationalPlanStatusBadge from '@/Components/OperationalPlanStatusBadge';
import OperationalPlanTaskList from '@/Components/OperationalPlanTaskList';
import OperationalPlanTypeBadge from '@/Components/OperationalPlanTypeBadge';
import OperationalPlanVisibilityBadge from '@/Components/OperationalPlanVisibilityBadge';
import PrimaryButton from '@/Components/PrimaryButton';
import AdminLayout from '@/Layouts/AdminLayout';
import { router } from '@inertiajs/react';

export default function OperationalPlansShow({ plan }: { plan: any }) {
    return (
        <AdminLayout
            title={plan.title}
            subtitle="Detalhe do plano operacional"
            headerActions={
                <div className="flex flex-wrap gap-2">
                    <PrimaryButton onClick={() => router.post(route('admin.operational-plans.approve', plan.id))}>Aprovar</PrimaryButton>
                    <PrimaryButton onClick={() => router.post(route('admin.operational-plans.complete', plan.id))}>Concluir</PrimaryButton>
                    <PrimaryButton onClick={() => router.post(route('admin.operational-plans.cancel', plan.id), { cancellation_reason: 'Cancelado manualmente' })}>Cancelar</PrimaryButton>
                </div>
            }
        >
            <section className="rounded-2xl border border-slate-200 bg-white p-6">
                <div className="flex flex-wrap gap-2">
                    <OperationalPlanTypeBadge type={plan.plan_type} />
                    <OperationalPlanStatusBadge status={plan.status} />
                    <OperationalPlanVisibilityBadge visibility={plan.visibility} />
                </div>
                <p className="mt-4 text-sm text-slate-600">{plan.description ?? 'Sem descrição.'}</p>
                <div className="mt-4 grid gap-4 md:grid-cols-3 text-sm text-slate-700">
                    <div>Período: {plan.start_date}{plan.end_date ? ` → ${plan.end_date}` : ''}</div>
                    <div>Responsável: {plan.owner?.name ?? '-'}</div>
                    <div>Departamento/Equipa: {plan.department?.name ?? '-'} / {plan.team?.name ?? '-'}</div>
                    <div>Ticket: {plan.related_ticket?.reference ?? '-'}</div>
                    <div>Espaço: {plan.related_space?.name ?? '-'}</div>
                    <div>Orçamento: {plan.budget_estimate ?? '-'}</div>
                </div>
                <div className="mt-4 max-w-sm">
                    <OperationalPlanProgressBar value={plan.progress_percent ?? 0} />
                </div>
            </section>

            <div className="mt-4 grid gap-4 xl:grid-cols-3">
                <section className="rounded-2xl border border-slate-200 bg-white p-6">
                    <h3 className="text-lg font-semibold text-slate-900">Tarefas Associadas</h3>
                    <div className="mt-3"><OperationalPlanTaskList tasks={plan.tasks ?? []} /></div>
                </section>
                <section className="rounded-2xl border border-slate-200 bg-white p-6">
                    <h3 className="text-lg font-semibold text-slate-900">Participantes</h3>
                    <div className="mt-3"><OperationalPlanParticipantsList participants={plan.participants ?? []} /></div>
                </section>
                <section className="rounded-2xl border border-slate-200 bg-white p-6">
                    <h3 className="text-lg font-semibold text-slate-900">Recursos</h3>
                    <div className="mt-3"><OperationalPlanResourcesList resources={plan.resources ?? []} /></div>
                </section>
            </div>
        </AdminLayout>
    );
}
