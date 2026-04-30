import InventoryTabs from '@/Components/InventoryTabs';
import AdminLayout from '@/Layouts/AdminLayout';
import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

export default function InventoryCategoriesCreate() {
    const form = useForm({ name: '', slug: '', description: '', is_active: true });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(route('admin.inventory-categories.store'));
    };

    return (
        <AdminLayout title="Nova Categoria" subtitle="Criar categoria de inventario">
            <InventoryTabs />
            <form onSubmit={submit} className="space-y-3 rounded-2xl border border-slate-200 bg-white p-4">
                <input value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} placeholder="Nome" className="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                <input value={form.data.slug} onChange={(event) => form.setData('slug', event.target.value)} placeholder="Slug" className="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                <textarea value={form.data.description} onChange={(event) => form.setData('description', event.target.value)} placeholder="Descricao" className="min-h-24 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                <button className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white">Guardar</button>
                <Link href={route('admin.inventory-categories.index')} className="ml-3 text-sm text-slate-700">Cancelar</Link>
            </form>
        </AdminLayout>
    );
}
