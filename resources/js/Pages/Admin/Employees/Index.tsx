import AdminLayout from '@/Layouts/AdminLayout';
import { Link, router } from '@inertiajs/react';

type Employee = {
    id: number;
    employee_number: string;
    role_title: string | null;
    employment_type: string;
    is_active: boolean;
    hire_date: string;
    user: { id: number; name: string } | null;
    department: { id: number; name: string } | null;
};

type DeptOption = { [key: number]: string };

type Props = {
    employees: { data: Employee[]; links: { url: string | null; label: string; active: boolean }[] };
    departments: DeptOption;
    filters: { department?: string; employment_type?: string; status?: string; search?: string };
};

export default function EmployeesIndex({ employees, departments, filters }: Props) {
    const filter = (key: string, value: string) =>
        router.get(route('admin.hr.employees.index'), { ...filters, [key]: value || undefined }, { preserveState: true, replace: true });

    return (
        <AdminLayout
            title="Funcionários"
            subtitle="Gestão dos funcionários da organização"
            headerActions={
                <Link href={route('admin.hr.employees.create')} className="inline-flex rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                    Novo Funcionário
                </Link>
            }
        >
            <div className="mb-4 flex flex-wrap gap-2 rounded-2xl border border-slate-200 bg-white p-4">
                <input
                    type="text"
                    defaultValue={filters.search ?? ''}
                    onChange={(e) => filter('search', e.target.value)}
                    placeholder="Pesquisar por nome ou nº"
                    className="rounded-xl border border-slate-300 px-3 py-2 text-sm"
                />
                <select defaultValue={filters.department ?? ''} onChange={(e) => filter('department', e.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Todos os departamentos</option>
                    {Object.entries(departments).map(([id, name]) => <option key={id} value={id}>{name}</option>)}
                </select>
                <select defaultValue={filters.status ?? ''} onChange={(e) => filter('status', e.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Todos os estados</option>
                    <option value="active">Ativo</option>
                    <option value="inactive">Inativo</option>
                </select>
            </div>

            <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                <table className="min-w-full divide-y divide-slate-200 text-sm">
                    <thead className="bg-slate-50 text-left text-slate-600">
                        <tr>
                            <th className="px-4 py-3">Nº Funcionário</th>
                            <th className="px-4 py-3">Nome</th>
                            <th className="px-4 py-3">Departamento</th>
                            <th className="px-4 py-3">Função</th>
                            <th className="px-4 py-3">Tipo</th>
                            <th className="px-4 py-3">Estado</th>
                            <th className="px-4 py-3">Ações</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                        {employees.data.length === 0 && (
                            <tr><td colSpan={7} className="px-4 py-6 text-center text-slate-500">Nenhum funcionário encontrado.</td></tr>
                        )}
                        {employees.data.map((emp) => (
                            <tr key={emp.id}>
                                <td className="px-4 py-3 font-mono text-slate-700">{emp.employee_number}</td>
                                <td className="px-4 py-3 font-medium text-slate-900">{emp.user?.name ?? '-'}</td>
                                <td className="px-4 py-3 text-slate-700">{emp.department?.name ?? '-'}</td>
                                <td className="px-4 py-3 text-slate-700">{emp.role_title ?? '-'}</td>
                                <td className="px-4 py-3 text-slate-700">{emp.employment_type}</td>
                                <td className="px-4 py-3">
                                    <span className={`inline-flex rounded-full px-2 py-0.5 text-xs font-medium ${emp.is_active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600'}`}>
                                        {emp.is_active ? 'Ativo' : 'Inativo'}
                                    </span>
                                </td>
                                <td className="px-4 py-3">
                                    <div className="flex gap-3">
                                        <Link href={route('admin.hr.employees.show', emp.id)} className="text-slate-700 hover:text-slate-950">Ver</Link>
                                        <Link href={route('admin.hr.employees.edit', emp.id)} className="text-slate-700 hover:text-slate-950">Editar</Link>
                                    </div>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            {employees.links.length > 3 && (
                <div className="mt-4 flex gap-1">
                    {employees.links.map((link, i) => (
                        link.url ? (
                            <Link key={i} href={link.url} className={`rounded px-3 py-1 text-sm ${link.active ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100'}`} dangerouslySetInnerHTML={{ __html: link.label }} />
                        ) : (
                            <span key={i} className="rounded px-3 py-1 text-sm text-slate-400" dangerouslySetInnerHTML={{ __html: link.label }} />
                        )
                    ))}
                </div>
            )}
        </AdminLayout>
    );
}
