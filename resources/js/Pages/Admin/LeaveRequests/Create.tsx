import AdminLayout from '@/Layouts/AdminLayout';
import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type EmpOption = { id: number; employee_number: string };
type AbsenceTypeOption = { id: number; name: string };

type Props = { employees: EmpOption[]; absenceTypes: AbsenceTypeOption[]; leaveTypes: string[] };

export default function LeaveRequestsCreate({ employees, absenceTypes, leaveTypes }: Props) {
    const form = useForm({
        employee_id: '', absence_type_id: '', leave_type: leaveTypes[0] ?? 'vacation',
        start_date: '', end_date: '', reason: '',
    });

    const submit = (e: FormEvent) => { e.preventDefault(); form.post(route('admin.hr.leave-requests.store')); };

    return (
        <AdminLayout title="Novo Pedido de Ausência" subtitle="Registar pedido de férias ou licença">
            <form onSubmit={submit} className="space-y-4 rounded-2xl border border-slate-200 bg-white p-6">
                <div className="grid gap-4 md:grid-cols-2">
                    <div className="flex flex-col gap-1">
                        <label className="text-xs font-medium text-slate-600">Funcionário *</label>
                        <select value={form.data.employee_id} onChange={(e) => form.setData('employee_id', e.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm" required>
                            <option value="">Selecionar</option>
                            {employees.map((e) => <option key={e.id} value={e.id}>{e.employee_number}</option>)}
                        </select>
                        {form.errors.employee_id && <span className="text-xs text-red-600">{form.errors.employee_id}</span>}
                    </div>
                    <div className="flex flex-col gap-1">
                        <label className="text-xs font-medium text-slate-600">Tipo de Ausência</label>
                        <select value={form.data.absence_type_id} onChange={(e) => form.setData('absence_type_id', e.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                            <option value="">Sem tipo específico</option>
                            {absenceTypes.map((t) => <option key={t.id} value={t.id}>{t.name}</option>)}
                        </select>
                    </div>
                    <div className="flex flex-col gap-1">
                        <label className="text-xs font-medium text-slate-600">Categoria *</label>
                        <select value={form.data.leave_type} onChange={(e) => form.setData('leave_type', e.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                            {leaveTypes.map((t) => <option key={t} value={t}>{t}</option>)}
                        </select>
                    </div>
                    <div className="flex flex-col gap-1">
                        <label className="text-xs font-medium text-slate-600">Data Início *</label>
                        <input type="date" value={form.data.start_date} onChange={(e) => form.setData('start_date', e.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm" required />
                        {form.errors.start_date && <span className="text-xs text-red-600">{form.errors.start_date}</span>}
                    </div>
                    <div className="flex flex-col gap-1">
                        <label className="text-xs font-medium text-slate-600">Data Fim *</label>
                        <input type="date" value={form.data.end_date} onChange={(e) => form.setData('end_date', e.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm" required />
                        {form.errors.end_date && <span className="text-xs text-red-600">{form.errors.end_date}</span>}
                    </div>
                    <div className="flex flex-col gap-1 md:col-span-2">
                        <label className="text-xs font-medium text-slate-600">Motivo</label>
                        <textarea value={form.data.reason} onChange={(e) => form.setData('reason', e.target.value)} className="min-h-20 rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                    </div>
                </div>

                <div className="flex items-center gap-3 pt-2">
                    <button type="submit" disabled={form.processing} className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 disabled:opacity-50">Submeter Pedido</button>
                    <Link href={route('admin.hr.leave-requests.index')} className="text-sm text-slate-700 hover:text-slate-950">Cancelar</Link>
                </div>
            </form>
        </AdminLayout>
    );
}
