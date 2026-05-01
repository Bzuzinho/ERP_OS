import AdminLayout from '@/Layouts/AdminLayout';
import { Link, router } from '@inertiajs/react';

type Employee = {
    id: number;
    employee_number: string;
    role_title: string | null;
    employment_type: string;
    hire_date: string;
    contract_end_date: string | null;
    weekly_hours: number;
    nif: string | null;
    is_active: boolean;
    user: { id: number; name: string; email: string } | null;
    department: { id: number; name: string } | null;
};

type Props = { employee: Employee };

export default function EmployeesShow({ employee }: Props) {
    const destroy = () => {
        if (confirm('Eliminar este funcionário?')) {
            router.delete(route('admin.hr.employees.destroy', employee.id));
        }
    };

    return (
        <AdminLayout
            title={employee.user?.name ?? `Funcionário ${employee.employee_number}`}
            subtitle={`Nº ${employee.employee_number}`}
            headerActions={
                <div className="flex gap-2">
                    <Link href={route('admin.hr.employees.edit', employee.id)} className="inline-flex rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Editar</Link>
                    <button onClick={destroy} className="rounded-xl border border-red-300 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50">Eliminar</button>
                </div>
            }
        >
            <div className="grid gap-4 md:grid-cols-2">
                <div className="rounded-2xl border border-slate-200 bg-white p-6">
                    <h2 className="mb-3 text-sm font-semibold text-slate-900">Dados Pessoais</h2>
                    <dl className="space-y-2 text-sm">
                        <div className="flex gap-2"><dt className="w-32 text-slate-500">Nome:</dt><dd>{employee.user?.name ?? '-'}</dd></div>
                        <div className="flex gap-2"><dt className="w-32 text-slate-500">Email:</dt><dd>{employee.user?.email ?? '-'}</dd></div>
                        <div className="flex gap-2"><dt className="w-32 text-slate-500">NIF:</dt><dd>{employee.nif ?? '-'}</dd></div>
                    </dl>
                </div>

                <div className="rounded-2xl border border-slate-200 bg-white p-6">
                    <h2 className="mb-3 text-sm font-semibold text-slate-900">Dados Contratuais</h2>
                    <dl className="space-y-2 text-sm">
                        <div className="flex gap-2"><dt className="w-32 text-slate-500">Departamento:</dt><dd>{employee.department?.name ?? '-'}</dd></div>
                        <div className="flex gap-2"><dt className="w-32 text-slate-500">Função:</dt><dd>{employee.role_title ?? '-'}</dd></div>
                        <div className="flex gap-2"><dt className="w-32 text-slate-500">Tipo:</dt><dd>{employee.employment_type}</dd></div>
                        <div className="flex gap-2"><dt className="w-32 text-slate-500">Admissão:</dt><dd>{employee.hire_date}</dd></div>
                        <div className="flex gap-2"><dt className="w-32 text-slate-500">Fim contrato:</dt><dd>{employee.contract_end_date ?? 'Indeterminado'}</dd></div>
                        <div className="flex gap-2"><dt className="w-32 text-slate-500">Horas/semana:</dt><dd>{employee.weekly_hours}h</dd></div>
                        <div className="flex gap-2">
                            <dt className="w-32 text-slate-500">Estado:</dt>
                            <dd><span className={`rounded-full px-2 py-0.5 text-xs font-medium ${employee.is_active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600'}`}>{employee.is_active ? 'Ativo' : 'Inativo'}</span></dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div className="mt-4">
                <Link href={route('admin.hr.employees.index')} className="text-sm text-slate-600 hover:text-slate-950">← Voltar</Link>
            </div>
        </AdminLayout>
    );
}
