import AdminLayout from '@/Layouts/AdminLayout';
import { Link, router } from '@inertiajs/react';

type Employee = { id: number; employee_number: string; role_title: string | null; is_active: boolean };
type Team = { id: number; name: string; is_active: boolean };
type Department = {
    id: number; name: string; description: string | null; is_active: boolean;
    employees: Employee[]; teams: Team[];
};

type Props = { department: Department };

export default function DepartmentsShow({ department }: Props) {
    const destroy = () => {
        if (confirm('Eliminar este departamento?')) {
            router.delete(route('admin.hr.departments.destroy', department.id));
        }
    };

    return (
        <AdminLayout
            title={department.name}
            subtitle="Detalhe do departamento"
            headerActions={
                <div className="flex gap-2">
                    <Link href={route('admin.hr.departments.edit', department.id)} className="inline-flex rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Editar</Link>
                    <button onClick={destroy} className="rounded-xl border border-red-300 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50">Eliminar</button>
                </div>
            }
        >
            <div className="grid gap-4 md:grid-cols-2">
                <div className="rounded-2xl border border-slate-200 bg-white p-6">
                    <h2 className="mb-3 text-sm font-semibold text-slate-900">Informação</h2>
                    <dl className="space-y-2 text-sm">
                        <div className="flex gap-2"><dt className="w-28 text-slate-500">Estado:</dt><dd>{department.is_active ? 'Ativo' : 'Inativo'}</dd></div>
                        {department.description && <div className="flex gap-2"><dt className="w-28 text-slate-500">Descrição:</dt><dd>{department.description}</dd></div>}
                    </dl>
                </div>

                <div className="rounded-2xl border border-slate-200 bg-white p-6">
                    <h2 className="mb-3 text-sm font-semibold text-slate-900">Equipas ({department.teams.length})</h2>
                    {department.teams.length === 0 ? (
                        <p className="text-sm text-slate-500">Sem equipas associadas.</p>
                    ) : (
                        <ul className="space-y-1">
                            {department.teams.map((t) => (
                                <li key={t.id}>
                                    <Link href={route('admin.hr.teams.show', t.id)} className="text-sm text-slate-700 hover:text-slate-950">{t.name}</Link>
                                </li>
                            ))}
                        </ul>
                    )}
                </div>

                <div className="rounded-2xl border border-slate-200 bg-white p-6 md:col-span-2">
                    <h2 className="mb-3 text-sm font-semibold text-slate-900">Funcionários ({department.employees.length})</h2>
                    {department.employees.length === 0 ? (
                        <p className="text-sm text-slate-500">Sem funcionários neste departamento.</p>
                    ) : (
                        <table className="min-w-full text-sm">
                            <thead className="text-left text-slate-500">
                                <tr>
                                    <th className="pb-2">Nº</th>
                                    <th className="pb-2">Função</th>
                                    <th className="pb-2">Estado</th>
                                    <th className="pb-2"></th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {department.employees.map((emp) => (
                                    <tr key={emp.id}>
                                        <td className="py-2 font-mono text-slate-700">{emp.employee_number}</td>
                                        <td className="py-2 text-slate-700">{emp.role_title ?? '-'}</td>
                                        <td className="py-2"><span className={`rounded-full px-2 py-0.5 text-xs ${emp.is_active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500'}`}>{emp.is_active ? 'Ativo' : 'Inativo'}</span></td>
                                        <td className="py-2"><Link href={route('admin.hr.employees.show', emp.id)} className="text-slate-600 hover:text-slate-950">Ver</Link></td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    )}
                </div>
            </div>

            <div className="mt-4">
                <Link href={route('admin.hr.departments.index')} className="text-sm text-slate-600 hover:text-slate-950">← Voltar</Link>
            </div>
        </AdminLayout>
    );
}
