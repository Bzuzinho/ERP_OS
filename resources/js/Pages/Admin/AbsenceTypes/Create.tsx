import AdminLayout from '@/Layouts/AdminLayout';
import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

export default function AbsenceTypesCreate() {
    const form = useForm({ name: '', slug: '', description: '', max_days_per_year: '', requires_proof: false, is_paid: true, color: '#6366f1', is_active: true });

    const submit = (e: FormEvent) => { e.preventDefault(); form.post(route('admin.hr.absence-types.store')); };

    return (
        <AdminLayout title="Novo Tipo de Ausência" subtitle="Definir um novo tipo de ausência ou licença">
            <form onSubmit={submit} className="space-y-4 rounded-2xl border border-slate-200 bg-white p-6">
                <div className="grid gap-4 md:grid-cols-2">
                    <div className="flex flex-col gap-1">
                        <label className="text-xs font-medium text-slate-600">Nome *</label>
                        <input value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} placeholder="Ex: Férias" className="rounded-xl border border-slate-300 px-3 py-2 text-sm" required />
                        {form.errors.name && <span className="text-xs text-red-600">{form.errors.name}</span>}
                    </div>
                    <div className="flex flex-col gap-1">
                        <label className="text-xs font-medium text-slate-600">Dias máximos por ano</label>
                        <input type="number" value={form.data.max_days_per_year} onChange={(e) => form.setData('max_days_per_year', e.target.value)} placeholder="Deixar vazio para ilimitado" className="rounded-xl border border-slate-300 px-3 py-2 text-sm" min={1} />
                    </div>
                    <div className="flex flex-col gap-1 md:col-span-2">
                        <label className="text-xs font-medium text-slate-600">Descrição</label>
                        <textarea value={form.data.description} onChange={(e) => form.setData('description', e.target.value)} className="min-h-20 rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                    </div>
                    <div className="flex flex-col gap-1">
                        <label className="text-xs font-medium text-slate-600">Cor</label>
                        <div className="flex gap-2">
                            <input type="color" value={form.data.color} onChange={(e) => form.setData('color', e.target.value)} className="h-10 w-14 rounded-xl border border-slate-300 p-1" />
                            <input value={form.data.color} onChange={(e) => form.setData('color', e.target.value)} className="flex-1 rounded-xl border border-slate-300 px-3 py-2 text-sm font-mono" />
                        </div>
                    </div>
                    <div className="flex flex-col gap-2 pt-1">
                        <label className="flex items-center gap-2 text-sm"><input type="checkbox" checked={form.data.requires_proof} onChange={(e) => form.setData('requires_proof', e.target.checked)} className="rounded" /> Requer comprovativo</label>
                        <label className="flex items-center gap-2 text-sm"><input type="checkbox" checked={form.data.is_paid} onChange={(e) => form.setData('is_paid', e.target.checked)} className="rounded" /> Ausência remunerada</label>
                        <label className="flex items-center gap-2 text-sm"><input type="checkbox" checked={form.data.is_active} onChange={(e) => form.setData('is_active', e.target.checked)} className="rounded" /> Tipo ativo</label>
                    </div>
                </div>

                <div className="flex items-center gap-3 pt-2">
                    <button type="submit" disabled={form.processing} className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 disabled:opacity-50">Guardar</button>
                    <Link href={route('admin.hr.absence-types.index')} className="text-sm text-slate-700 hover:text-slate-950">Cancelar</Link>
                </div>
            </form>
        </AdminLayout>
    );
}
