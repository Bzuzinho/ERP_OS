import InventoryTabs from '@/Components/InventoryTabs';
import AdminLayout from '@/Layouts/AdminLayout';
import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type Props = { location: { id: number; name: string; slug: string; address: string | null; responsible_user_id: number | null; description: string | null }; users: { id: number; name: string }[] };

export default function InventoryLocationsEdit({ location, users }: Props) {
    const form = useForm({ name: location.name, slug: location.slug, description: location.description ?? '', address: location.address ?? '', responsible_user_id: location.responsible_user_id ? String(location.responsible_user_id) : '' });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.put(route('admin.inventory-locations.update', location.id));
    };

    return (
        <AdminLayout title="Editar Localizacao" subtitle="Atualizar localizacao de armazem">
            <InventoryTabs />
            <form onSubmit={submit} className="space-y-3 rounded-2xl border border-slate-200 bg-white p-4">
                <input value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} className="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                <input value={form.data.slug} onChange={(event) => form.setData('slug', event.target.value)} className="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                <textarea value={form.data.address} onChange={(event) => form.setData('address', event.target.value)} className="min-h-20 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                <select value={form.data.responsible_user_id} onChange={(event) => form.setData('responsible_user_id', event.target.value)} className="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"><option value="">Responsavel</option>{users.map((item) => <option key={item.id} value={item.id}>{item.name}</option>)}</select>
                <button className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white">Guardar</button>
                <Link href={route('admin.inventory-locations.index')} className="ml-3 text-sm text-slate-700">Cancelar</Link>
            </form>
        </AdminLayout>
    );
}
