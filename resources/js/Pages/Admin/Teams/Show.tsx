import AdminLayout from '@/Layouts/AdminLayout';
import { Link, router, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type Member = { id: number; employee_id: number; role: string | null; employee: { id: number; employee_number: string; role_title: string | null } };
type EmpOption = { id: number; employee_number: string; role_title: string | null };
type Team = { id: number; name: string; is_active: boolean; department: { id: number; name: string } | null };

type Props = {
    team: Team;
    activeMembers: Member[];
    inactiveMembers: Member[];
    availableEmployees: EmpOption[];
};

export default function TeamsShow({ team, activeMembers, inactiveMembers, availableEmployees }: Props) {
    const addForm = useForm({ employee_id: '', role: '' });

    const submitAdd = (e: FormEvent) => {
        e.preventDefault();
        addForm.post(route('admin.hr.teams.members.add', team.id), { onSuccess: () => addForm.reset() });
    };

    const removeMember = (employeeId: number) => {
        if (confirm('Remover este funcionário da equipa?')) {
            router.delete(route('admin.hr.teams.members.remove', team.id), { data: { employee_id: employeeId } });
        }
    };

    const destroy = () => {
        if (confirm('Eliminar esta equipa?')) router.delete(route('admin.hr.teams.destroy', team.id));
    };

    return (
        <AdminLayout
            title={team.name}
            subtitle={team.department?.name ?? 'Sem departamento'}
            headerActions={
                <div className="flex gap-2">
                    <Link href={route('admin.hr.teams.edit', team.id)} className="inline-flex rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Editar</Link>
                    <button onClick={destroy} className="rounded-xl border border-red-300 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50">Eliminar</button>
                </div>
            }
        >
            <div className="grid gap-4 lg:grid-cols-3">
                <div className="lg:col-span-2 space-y-4">
                    <div className="rounded-2xl border border-slate-200 bg-white p-6">
                        <h2 className="mb-3 text-sm font-semibold text-slate-900">Membros Ativos ({activeMembers.length})</h2>
                        {activeMembers.length === 0 ? (
                            <p className="text-sm text-slate-500">Sem membros ativos.</p>
                        ) : (
                            <table className="min-w-full text-sm">
                                <thead className="text-left text-slate-500"><tr><th className="pb-2">Nº</th><th className="pb-2">Função na equipa</th><th className="pb-2"></th></tr></thead>
                                <tbody className="divide-y divide-slate-100">
                                    {activeMembers.map((m) => (
                                        <tr key={m.id}>
                                            <td className="py-2 font-mono text-slate-700">{m.employee.employee_number}</td>
                                            <td className="py-2 text-slate-700">{m.role ?? m.employee.role_title ?? '-'}</td>
                                            <td className="py-2">
                                                <button onClick={() => removeMember(m.employee_id)} className="text-xs text-red-600 hover:text-red-800">Remover</button>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        )}
                    </div>
                </div>

                <div className="space-y-4">
                    <form onSubmit={submitAdd} className="rounded-2xl border border-slate-200 bg-white p-6">
                        <h2 className="mb-3 text-sm font-semibold text-slate-900">Adicionar Membro</h2>
                        <div className="space-y-3">
                            <select value={addForm.data.employee_id} onChange={(e) => addForm.setData('employee_id', e.target.value)} className="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" required>
                                <option value="">Selecionar funcionário</option>
                                {availableEmployees.map((e) => <option key={e.id} value={e.id}>{e.employee_number} — {e.role_title ?? '-'}</option>)}
                            </select>
                            <input value={addForm.data.role} onChange={(e) => addForm.setData('role', e.target.value)} placeholder="Função na equipa (opcional)" className="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                            <button type="submit" disabled={addForm.processing} className="w-full rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 disabled:opacity-50">Adicionar</button>
                        </div>
                    </form>
                </div>
            </div>

            <div className="mt-4">
                <Link href={route('admin.hr.teams.index')} className="text-sm text-slate-600 hover:text-slate-950">← Voltar</Link>
            </div>
        </AdminLayout>
    );
}
