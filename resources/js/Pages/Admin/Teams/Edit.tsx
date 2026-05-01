import AdminLayout from '@/Layouts/AdminLayout';
import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type DeptOption = { id: number; name: string };
type UserOption = { id: number; name: string };
type Team = { id: number; name: string; slug: string; description: string | null; department_id: number | null; leader_id: number | null; is_active: boolean };

type Props = { team: Team; departments: DeptOption[]; users: UserOption[] };

export default function TeamsEdit({ team, departments, users }: Props) {
    const form = useForm({
        name: team.name, slug: team.slug, description: team.description ?? '',
        department_id: team.department_id?.toString() ?? '', leader_id: team.leader_id?.toString() ?? '',
        is_active: team.is_active,
    });

    const submit = (e: FormEvent) => { e.preventDefault(); form.put(route('admin.hr.teams.update', team.id)); };

    return (
        <AdminLayout title="Editar Equipa" subtitle={team.name}>
            <form onSubmit={submit} className="space-y-4 rounded-2xl border border-slate-200 bg-white p-6">
                <div className="grid gap-4 md:grid-cols-2">
                    <div className="flex flex-col gap-1">
                        <label className="text-xs font-medium text-slate-600">Nome *</label>
                        <input value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm" required />
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
                    <Link href={route('admin.hr.teams.show', team.id)} className="text-sm text-slate-700 hover:text-slate-950">Cancelar</Link>
                </div>
            </form>
        </AdminLayout>
    );
}
