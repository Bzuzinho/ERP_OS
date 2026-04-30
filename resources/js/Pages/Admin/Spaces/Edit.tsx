import AdminLayout from '@/Layouts/AdminLayout';
import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';

type Space = { id: number; name: string; slug: string; description: string | null; location_text: string | null; capacity: number | null; status: string; is_public: boolean; is_active: boolean; };
type Props = { space: Space; statuses: string[] };

export default function AdminSpacesEdit({ space, statuses }: Props) {
    const { data, setData, put, processing } = useForm({ name: space.name, slug: space.slug, description: space.description ?? '', location_text: space.location_text ?? '', capacity: space.capacity ?? 0, status: space.status, is_public: space.is_public, is_active: space.is_active });

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        put(route('admin.spaces.update', space.id));
    };

    return (
        <AdminLayout title="Editar Espaco" subtitle={space.name}>
            <form onSubmit={submit} className="space-y-3 rounded-2xl border border-slate-200 bg-white p-4">
                <input value={data.name} onChange={(event) => setData('name', event.target.value)} className="w-full rounded-lg border-slate-300 text-sm" />
                <input value={data.slug} onChange={(event) => setData('slug', event.target.value)} className="w-full rounded-lg border-slate-300 text-sm" />
                <select value={data.status} onChange={(event) => setData('status', event.target.value)} className="w-full rounded-lg border-slate-300 text-sm">{statuses.map((status) => <option key={status} value={status}>{status}</option>)}</select>
                <button disabled={processing} className="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white">Atualizar</button>
            </form>
        </AdminLayout>
    );
}
