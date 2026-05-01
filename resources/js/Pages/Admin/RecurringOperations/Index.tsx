import RecurringOperationFilters from '@/Components/RecurringOperationFilters';
import RecurringOperationFrequencyBadge from '@/Components/RecurringOperationFrequencyBadge';
import RecurringOperationStatusBadge from '@/Components/RecurringOperationStatusBadge';
import AdminLayout from '@/Layouts/AdminLayout';
import { Link, router } from '@inertiajs/react';

export default function RecurringOperationsIndex({ operations, filters, statuses, frequencies, types }: { operations: { data: any[] }; filters: Record<string, string | null | undefined>; statuses: string[]; frequencies: string[]; types: string[] }) {
    const onFilterChange = (name: string, value: string) => {
        router.get(route('admin.recurring-operations.index'), { ...filters, [name]: value || undefined }, { preserveState: true, replace: true });
    };

    return (
        <AdminLayout title="Operações Recorrentes" subtitle="Automação simples de tarefas e eventos">
            <div className="mb-4 flex items-center justify-between">
                <h2 className="text-xl font-semibold text-slate-900">Recorrências</h2>
                <Link href={route('admin.recurring-operations.create')} className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Nova Recorrência</Link>
            </div>

            <RecurringOperationFilters filters={filters} statuses={statuses} frequencies={frequencies} types={types} onChange={onFilterChange} />

            <div className="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white">
                <table className="min-w-full text-sm">
                    <thead className="bg-slate-50 text-left text-slate-600"><tr><th className="px-4 py-3">Título</th><th className="px-4 py-3">Tipo</th><th className="px-4 py-3">Frequência</th><th className="px-4 py-3">Próxima</th><th className="px-4 py-3">Última</th><th className="px-4 py-3">Estado</th><th className="px-4 py-3">Responsável</th></tr></thead>
                    <tbody>
                        {operations.data.map((operation) => (
                            <tr key={operation.id} className="border-t border-slate-200">
                                <td className="px-4 py-3"><Link href={route('admin.recurring-operations.show', operation.id)} className="font-medium text-slate-900 hover:underline">{operation.title}</Link></td>
                                <td className="px-4 py-3">{operation.operation_type}</td>
                                <td className="px-4 py-3"><RecurringOperationFrequencyBadge frequency={operation.frequency} interval={operation.interval} /></td>
                                <td className="px-4 py-3">{operation.next_run_at ? new Date(operation.next_run_at).toLocaleString() : '-'}</td>
                                <td className="px-4 py-3">{operation.last_run_at ? new Date(operation.last_run_at).toLocaleString() : '-'}</td>
                                <td className="px-4 py-3"><RecurringOperationStatusBadge status={operation.status} /></td>
                                <td className="px-4 py-3">{operation.owner?.name ?? '-'}</td>
                            </tr>
                        ))}
                        {operations.data.length === 0 ? <tr><td colSpan={7} className="px-4 py-6 text-center text-slate-500">Sem operações recorrentes.</td></tr> : null}
                    </tbody>
                </table>
            </div>
        </AdminLayout>
    );
}
