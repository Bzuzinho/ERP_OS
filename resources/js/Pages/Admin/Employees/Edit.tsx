import AdminLayout from '@/Layouts/AdminLayout';
import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type UserOption = { id: number; name: string };
type DeptOption = { id: number; name: string };
type Employee = {
    id: number; user_id: number; department_id: number | null; role_title: string | null;
    employment_type: string; hire_date: string; contract_end_date: string | null;
    weekly_hours: number; nif: string | null; iban: string | null; notes: string | null; is_active: boolean;
};

type Props = { employee: Employee; users: UserOption[]; departments: DeptOption[]; employmentTypes: string[] };

export default function EmployeesEdit({ employee, users, departments, employmentTypes }: Props) {
    const form = useForm({
        user_id: employee.user_id.toString(),
        department_id: employee.department_id?.toString() ?? '',
        role_title: employee.role_title ?? '',
        employment_type: employee.employment_type,
        hire_date: employee.hire_date,
        contract_end_date: employee.contract_end_date ?? '',
        weekly_hours: employee.weekly_hours.toString(),
        nif: employee.nif ?? '',
        iban: employee.iban ?? '',
        notes: employee.notes ?? '',
        is_active: employee.is_active,
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        form.put(route('admin.hr.employees.update', employee.id));
    };

    return (
        <AdminLayout title="Editar Funcionário" subtitle={`Nº ${employee.id}`}>
            <form onSubmit={submit} className="space-y-4 rounded-2xl border border-slate-200 bg-white p-6">
                <div className="grid gap-4 md:grid-cols-2">
                    <div className="flex flex-col gap-1">
                        <label className="text-xs font-medium text-slate-600">Utilizador *</label>
                        <select value={form.data.user_id} onChange={(e) => form.setData('user_id', e.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm" required>
                            {users.map((u) => <option key={u.id} value={u.id}>{u.name}</option>)}
                        </select>
                    </div>
                    <div className="flex flex-col gap-1">
                        <label className="text-xs font-medium text-slate-600">Departamento</label>
                        <select value={form.data.department_id} onChange={(e) => form.setData('department_id', e.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                            <option value="">Sem departamento</option>
                            {departments.map((d) => <option key={d.id} value={d.id}>{d.name}</option>)}
                        </select>
                    </div>
                    <div className="flex flex-col gap-1">
                        <label className="text-xs font-medium text-slate-600">Função / Cargo</label>
                        <input value={form.data.role_title} onChange={(e) => form.setData('role_title', e.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                    </div>
                    <div className="flex flex-col gap-1">
                        <label className="text-xs font-medium text-slate-600">Tipo de Contrato</label>
                        <select value={form.data.employment_type} onChange={(e) => form.setData('employment_type', e.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                            {employmentTypes.map((t) => <option key={t} value={t}>{t}</option>)}
                        </select>
                    </div>
                    <div className="flex flex-col gap-1">
                        <label className="text-xs font-medium text-slate-600">Data de Admissão</label>
                        <input type="date" value={form.data.hire_date} onChange={(e) => form.setData('hire_date', e.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                    </div>
                    <div className="flex flex-col gap-1">
                        <label className="text-xs font-medium text-slate-600">Fim de Contrato</label>
                        <input type="date" value={form.data.contract_end_date} onChange={(e) => form.setData('contract_end_date', e.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                    </div>
                    <div className="flex flex-col gap-1">
                        <label className="text-xs font-medium text-slate-600">Horas Semanais</label>
                        <input type="number" value={form.data.weekly_hours} onChange={(e) => form.setData('weekly_hours', e.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm" min={0} max={60} />
                    </div>
                    <div className="flex flex-col gap-1">
                        <label className="text-xs font-medium text-slate-600">NIF</label>
                        <input value={form.data.nif} onChange={(e) => form.setData('nif', e.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                    </div>
                    <div className="flex flex-col gap-1">
                        <label className="text-xs font-medium text-slate-600">IBAN</label>
                        <input value={form.data.iban} onChange={(e) => form.setData('iban', e.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                    </div>
                    <div className="flex flex-col gap-1 md:col-span-2">
                        <label className="text-xs font-medium text-slate-600">Notas</label>
                        <textarea value={form.data.notes} onChange={(e) => form.setData('notes', e.target.value)} className="min-h-20 rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                    </div>
                    <div className="flex items-center gap-2">
                        <input type="checkbox" id="is_active" checked={form.data.is_active} onChange={(e) => form.setData('is_active', e.target.checked)} className="rounded" />
                        <label htmlFor="is_active" className="text-sm text-slate-700">Funcionário ativo</label>
                    </div>
                </div>

                <div className="flex items-center gap-3 pt-2">
                    <button type="submit" disabled={form.processing} className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 disabled:opacity-50">Guardar</button>
                    <Link href={route('admin.hr.employees.show', employee.id)} className="text-sm text-slate-700 hover:text-slate-950">Cancelar</Link>
                </div>
            </form>
        </AdminLayout>
    );
}
