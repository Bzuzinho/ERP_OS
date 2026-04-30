import AdminLayout from '@/Layouts/AdminLayout';
import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';

type Props = { statuses: string[] };

export default function AdminSpacesCreate({ statuses }: Props) {
    const { data, setData, post, processing } = useForm({ name: '', slug: '', description: '', location_text: '', capacity: '', status: statuses[0] ?? 'available', requires_approval: true, has_cleaning_required: false, is_public: false, is_active: true });

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        post(route('admin.spaces.store'));
    };

    return (
        <AdminLayout title="Criar Espaco" subtitle="Novo espaco da junta">
            <form onSubmit={submit} className="space-y-3 rounded-2xl border border-slate-200 bg-white p-4">
                <input value={data.name} onChange={(event) => setData('name', event.target.value)} placeholder="Nome" className="w-full rounded-lg border-slate-300 text-sm" />
                <input value={data.slug} onChange={(event) => setData('slug', event.target.value)} placeholder="Slug" className="w-full rounded-lg border-slate-300 text-sm" />
                <input value={data.location_text} onChange={(event) => setData('location_text', event.target.value)} placeholder="Localizacao" className="w-full rounded-lg border-slate-300 text-sm" />
                <input type="number" value={data.capacity} onChange={(event) => setData('capacity', event.target.value)} placeholder="Capacidade" className="w-full rounded-lg border-slate-300 text-sm" />
                <select value={data.status} onChange={(event) => setData('status', event.target.value)} className="w-full rounded-lg border-slate-300 text-sm">{statuses.map((status) => <option key={status} value={status}>{status}</option>)}</select>
                <textarea value={data.description} onChange={(event) => setData('description', event.target.value)} placeholder="Descricao" className="w-full rounded-lg border-slate-300 text-sm" />
                <button disabled={processing} className="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white">Guardar</button>
            </form>
        </AdminLayout>
    );
}
