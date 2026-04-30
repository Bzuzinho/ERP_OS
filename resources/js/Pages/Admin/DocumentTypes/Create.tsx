import AdminLayout from '@/Layouts/AdminLayout';
import { useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

export default function DocumentTypeCreate() {
    const form = useForm({ name: '', slug: '', description: '', is_active: true });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(route('admin.document-types.store'));
    };

    return (
        <AdminLayout title="Criar Tipo de Documento">
            <form onSubmit={submit} className="max-w-lg space-y-5 rounded-2xl border border-slate-200 bg-white p-6">
                <div>
                    <label className="block text-sm font-medium text-slate-700">Nome *</label>
                    <input type="text" value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} required className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                    {form.errors.name ? <p className="mt-1 text-xs text-rose-600">{form.errors.name}</p> : null}
                </div>
                <div>
                    <label className="block text-sm font-medium text-slate-700">Slug</label>
                    <input type="text" value={form.data.slug} onChange={(e) => form.setData('slug', e.target.value)} placeholder="gerado automaticamente" className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                    {form.errors.slug ? <p className="mt-1 text-xs text-rose-600">{form.errors.slug}</p> : null}
                </div>
                <div>
                    <label className="block text-sm font-medium text-slate-700">Descrição</label>
                    <textarea value={form.data.description} onChange={(e) => form.setData('description', e.target.value)} rows={3} className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                </div>
                <div className="flex items-center gap-2">
                    <input type="checkbox" id="is_active" checked={form.data.is_active} onChange={(e) => form.setData('is_active', e.target.checked)} className="rounded" />
                    <label htmlFor="is_active" className="text-sm font-medium text-slate-700">Ativo</label>
                </div>
                <div className="flex gap-3">
                    <button type="submit" disabled={form.processing} className="rounded-xl bg-slate-900 px-5 py-2 text-sm font-medium text-white hover:bg-slate-700 disabled:opacity-50">Criar</button>
                    <a href={route('admin.document-types.index')} className="rounded-xl border border-slate-300 px-5 py-2 text-sm text-slate-700 hover:bg-slate-50">Cancelar</a>
                </div>
            </form>
        </AdminLayout>
    );
}
