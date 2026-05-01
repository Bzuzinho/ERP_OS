import AdminLayout from '@/Layouts/AdminLayout';
import { Link, router } from '@inertiajs/react';

type AttendanceRecord = {
    id: number; date: string; status: string; check_in: string | null; check_out: string | null;
    worked_minutes: number | null; notes: string | null; validated_at: string | null;
    employee: { id: number; employee_number: string } | null;
    validator: { id: number; name: string } | null;
};

type Props = { record: AttendanceRecord };

export default function AttendanceShow({ record }: Props) {
    const validate = () => {
        router.patch(route('admin.hr.attendance.validate', record.id));
    };

    return (
        <AdminLayout
            title="Registo de Presença"
            subtitle={`${record.employee?.employee_number ?? '-'} — ${record.date}`}
            headerActions={
                <div className="flex gap-2">
                    <Link href={route('admin.hr.attendance.edit', record.id)} className="inline-flex rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Editar</Link>
                    {!record.validated_at && (
                        <button onClick={validate} className="rounded-xl border border-blue-300 px-4 py-2 text-sm font-medium text-blue-700 hover:bg-blue-50">Validar</button>
                    )}
                </div>
            }
        >
            <div className="rounded-2xl border border-slate-200 bg-white p-6">
                <dl className="grid gap-3 text-sm md:grid-cols-2">
                    <div className="flex gap-2"><dt className="w-32 text-slate-500">Funcionário:</dt><dd>{record.employee?.employee_number ?? '-'}</dd></div>
                    <div className="flex gap-2"><dt className="w-32 text-slate-500">Data:</dt><dd>{record.date}</dd></div>
                    <div className="flex gap-2"><dt className="w-32 text-slate-500">Estado:</dt><dd><span className="rounded-full bg-slate-100 px-2 py-0.5 text-xs">{record.status}</span></dd></div>
                    <div className="flex gap-2"><dt className="w-32 text-slate-500">Entrada:</dt><dd>{record.check_in ?? '-'}</dd></div>
                    <div className="flex gap-2"><dt className="w-32 text-slate-500">Saída:</dt><dd>{record.check_out ?? '-'}</dd></div>
                    <div className="flex gap-2"><dt className="w-32 text-slate-500">Horas trab.:</dt><dd>{record.worked_minutes ? `${Math.floor(record.worked_minutes / 60)}h${record.worked_minutes % 60}m` : '-'}</dd></div>
                    <div className="flex gap-2"><dt className="w-32 text-slate-500">Validado por:</dt><dd>{record.validator?.name ?? 'Não validado'}</dd></div>
                    {record.notes && <div className="flex gap-2 md:col-span-2"><dt className="w-32 text-slate-500">Notas:</dt><dd>{record.notes}</dd></div>}
                </dl>
            </div>

            <div className="mt-4">
                <Link href={route('admin.hr.attendance.index')} className="text-sm text-slate-600 hover:text-slate-950">← Voltar</Link>
            </div>
        </AdminLayout>
    );
}
