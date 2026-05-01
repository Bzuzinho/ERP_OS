import AdminLayout from '@/Layouts/AdminLayout';
import { useForm } from '@inertiajs/react';

export default function RecurringOperationsEdit({ operation, types, statuses, frequencies }: { operation: any; types: string[]; statuses: string[]; frequencies: string[] }) {
    const form = useForm({
        title: operation.title ?? '',
        description: operation.description ?? '',
        operation_type: operation.operation_type ?? (types[0] ?? 'task'),
        status: operation.status ?? (statuses[0] ?? 'active'),
        frequency: operation.frequency ?? (frequencies[0] ?? 'weekly'),
        interval: operation.interval ?? 1,
        start_date: operation.start_date ?? '',
        end_date: operation.end_date ?? '',
        task_template: operation.task_template ?? {},
        event_template: operation.event_template ?? {},
    });

    return (
        <AdminLayout title="Editar Recorrência" subtitle="Atualizar operação recorrente">
            <form onSubmit={(e) => { e.preventDefault(); form.put(route('admin.recurring-operations.update', operation.id)); }} className="space-y-4 rounded-2xl border border-slate-200 bg-white p-6">
                <input className="w-full rounded-lg border-slate-300" value={form.data.title} onChange={(e) => form.setData('title', e.target.value)} placeholder="Título" />
                <textarea className="w-full rounded-lg border-slate-300" value={form.data.description} onChange={(e) => form.setData('description', e.target.value)} placeholder="Descrição" />
                <div className="grid gap-3 md:grid-cols-3">
                    <select className="rounded-lg border-slate-300" value={form.data.operation_type} onChange={(e) => form.setData('operation_type', e.target.value)}>{types.map((type) => <option key={type} value={type}>{type}</option>)}</select>
                    <select className="rounded-lg border-slate-300" value={form.data.status} onChange={(e) => form.setData('status', e.target.value)}>{statuses.map((status) => <option key={status} value={status}>{status}</option>)}</select>
                    <select className="rounded-lg border-slate-300" value={form.data.frequency} onChange={(e) => form.setData('frequency', e.target.value)}>{frequencies.map((frequency) => <option key={frequency} value={frequency}>{frequency}</option>)}</select>
                </div>
                <div className="grid gap-3 md:grid-cols-3">
                    <input type="number" min={1} className="w-full rounded-lg border-slate-300" value={form.data.interval} onChange={(e) => form.setData('interval', Number(e.target.value))} />
                    <input type="date" className="w-full rounded-lg border-slate-300" value={form.data.start_date} onChange={(e) => form.setData('start_date', e.target.value)} />
                    <input type="date" className="w-full rounded-lg border-slate-300" value={form.data.end_date} onChange={(e) => form.setData('end_date', e.target.value)} />
                </div>
                <button type="submit" className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Guardar Alterações</button>
            </form>
        </AdminLayout>
    );
}
