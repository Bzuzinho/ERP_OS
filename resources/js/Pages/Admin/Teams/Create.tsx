import AdminLayout from '@/Layouts/AdminLayout';
import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type DeptOption = { id: number; name: string };
type UserOption = { id: number; name: string };

type Props = { departments: DeptOption[]; users: UserOption[] };

export default function TeamsCreate({ departments, users }: Props) {
    const form = useForm({ name: '', slug: '', description: '', department_id: '', leader_id: '', is_active: true });

    const submit = (e: FormEvent) => { e.preventDefault(); form.post(route('admin.hr.teams.store')); };

    return (
        <AdminLayout title="Nova Equipa" subtitle="Criar uma nova equipa de trabalho">
            <form onSubmit={submit} className="space-y-4 rounded-2xl border border-slate-200 bg-white p-6">
                <div className="grid gap-4 md:grid-cols-2">
                    <div className="flex flex-col gap-1">
                        <label className="text-xs font-medium text-slate-600">Nome *</label>
                        <input value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} placeholder="Ex: Equipa de Manutenção" className="rounded-xl border border-slate-300 px-3 py-2 text-sm" required />
                        {form.errors.name && <span className="text-xs text-red-600">{form.errors.name}</span>}
                    </div>
                    <div className="flex flex-col gap-1">
                        <label className="text-xs font-medium text-slate-600">Departamento</label>
                        <select value={form.data.department_id} onChange={(e) => form.setData('department_id', e.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                            <option value="">Sem departamento</option>
                            {departments.map((d) => <option key={d.id} value={d.id}>{d.name}</option>)}
                        </select>
                    </div>
                    <div className="flex flex-col gap-1">
                        <label className="text-xs font-medium text-slate-600">Líder</label>
                        <select value={form.data.leader_id} onChange={(e) => form.setData('leader_id', e.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                            <option value="">Sem líder</option>
                            {users.map((u) => <option key={u.id} value={u.id}>{u.name}</option>)}
                        </select>
                    </div>
                    <div className="flex items-center gap-2 pt-5">
                        <input type="checkbox" id="is_active" checked={form.data.is_active} onChange={(e) => form.setData('is_active', e.target.checked)} className="rounded" />
                        <label htmlFor="is_active" className="text-sm text-slate-700">Equipa ativa</label>
                    </div>
                    <div className="flex flex-col gap-1 md:col-span-2">
                        <label className="text-xs font-medium text-slate-600">Descrição</label>
                        <textarea value={form.data.description} onChange={(e) => form.setData('description', e.target.value)} className="min-h-20 rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                    </div>
                </div>

                <div className="flex items-center gap-3 pt-2">
                    <button type="submit" disabled={form.processing} className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 disabled:opacity-50">Guardar</button>
                    <Link href={route('admin.hr.teams.index')} className="text-sm text-slate-700 hover:text-slate-950">Cancelar</Link>
                </div>
            </form>
        </AdminLayout>
    );
}
