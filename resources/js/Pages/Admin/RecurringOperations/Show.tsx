import RecurringOperationRunList from '@/Components/RecurringOperationRunList';
import RecurringOperationStatusBadge from '@/Components/RecurringOperationStatusBadge';
import AdminLayout from '@/Layouts/AdminLayout';
import { router } from '@inertiajs/react';

export default function RecurringOperationsShow({ operation }: { operation: any }) {
    return (
        <AdminLayout
            title={operation.title}
            subtitle="Detalhe da operação recorrente"
            headerActions={
                <div className="flex flex-wrap gap-2">
                    <button type="button" onClick={() => router.post(route('admin.recurring-operations.pause', operation.id))} className="rounded-xl bg-amber-600 px-4 py-2 text-sm font-semibold text-white">Pausar</button>
                    <button type="button" onClick={() => router.post(route('admin.recurring-operations.resume', operation.id))} className="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white">Retomar</button>
                    <button type="button" onClick={() => router.post(route('admin.recurring-operations.cancel', operation.id))} className="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white">Cancelar</button>
                    <button type="button" onClick={() => router.post(route('admin.recurring-operations.runs.store', operation.id))} className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Gerar Execução</button>
                </div>
            }
        >
            <section className="rounded-2xl border border-slate-200 bg-white p-6">
                <div className="flex items-center gap-3"><RecurringOperationStatusBadge status={operation.status} /><span className="text-sm text-slate-600">{operation.operation_type} • {operation.frequency} ({operation.interval})</span></div>
                <p className="mt-3 text-sm text-slate-600">{operation.description ?? 'Sem descrição.'}</p>
                <div className="mt-4 grid gap-3 text-sm text-slate-700 md:grid-cols-2">
                    <div>Próxima execução: {operation.next_run_at ? new Date(operation.next_run_at).toLocaleString() : '-'}</div>
                    <div>Última execução: {operation.last_run_at ? new Date(operation.last_run_at).toLocaleString() : '-'}</div>
                </div>
                <pre className="mt-4 overflow-auto rounded-xl bg-slate-900 p-4 text-xs text-slate-100">{JSON.stringify({ task_template: operation.task_template, event_template: operation.event_template }, null, 2)}</pre>
            </section>

            <section className="mt-4 rounded-2xl border border-slate-200 bg-white p-6">
                <h3 className="text-lg font-semibold text-slate-900">Execuções Recentes</h3>
                <div className="mt-3"><RecurringOperationRunList runs={operation.runs ?? []} /></div>
            </section>
        </AdminLayout>
    );
}
