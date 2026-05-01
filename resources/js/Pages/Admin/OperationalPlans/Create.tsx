import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import AdminLayout from '@/Layouts/AdminLayout';
import { useForm } from '@inertiajs/react';

export default function OperationalPlansCreate({ types, statuses, visibilities }: { types: string[]; statuses: string[]; visibilities: string[] }) {
    const form = useForm({
        title: '',
        plan_type: types[0] ?? 'activity',
        status: statuses.includes('draft') ? 'draft' : statuses[0],
        visibility: visibilities.includes('internal') ? 'internal' : visibilities[0],
        start_date: '',
        end_date: '',
        description: '',
    });

    return (
        <AdminLayout title="Novo Plano" subtitle="Criar plano operacional">
            <form
                onSubmit={(e) => {
                    e.preventDefault();
                    form.post(route('admin.operational-plans.store'));
                }}
                className="space-y-4 rounded-2xl border border-slate-200 bg-white p-6"
            >
                <TextInput value={form.data.title} onChange={(e) => form.setData('title', e.target.value)} placeholder="Título" />
                <textarea className="w-full rounded-lg border-slate-300" value={form.data.description} onChange={(e) => form.setData('description', e.target.value)} placeholder="Descrição" />
                <div className="grid gap-3 md:grid-cols-3">
                    <select className="rounded-lg border-slate-300" value={form.data.plan_type} onChange={(e) => form.setData('plan_type', e.target.value)}>{types.map((type) => <option key={type} value={type}>{type}</option>)}</select>
                    <select className="rounded-lg border-slate-300" value={form.data.status} onChange={(e) => form.setData('status', e.target.value)}>{statuses.map((status) => <option key={status} value={status}>{status}</option>)}</select>
                    <select className="rounded-lg border-slate-300" value={form.data.visibility} onChange={(e) => form.setData('visibility', e.target.value)}>{visibilities.map((visibility) => <option key={visibility} value={visibility}>{visibility}</option>)}</select>
                </div>
                <div className="grid gap-3 md:grid-cols-2">
                    <TextInput type="date" value={form.data.start_date} onChange={(e) => form.setData('start_date', e.target.value)} />
                    <TextInput type="date" value={form.data.end_date} onChange={(e) => form.setData('end_date', e.target.value)} />
                </div>
                <PrimaryButton type="submit" disabled={form.processing}>Guardar Plano</PrimaryButton>
            </form>
        </AdminLayout>
    );
}
