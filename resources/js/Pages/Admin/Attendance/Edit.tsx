import AdminLayout from '@/Layouts/AdminLayout';
import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type AttendanceRecord = {
    id: number; date: string; status: string; check_in: string | null; check_out: string | null;
    break_minutes: number; notes: string | null;
};

type Props = { record: AttendanceRecord; statuses: string[] };

export default function AttendanceEdit({ record, statuses }: Props) {
    const form = useForm({
        status: record.status,
        check_in: record.check_in ?? '',
        check_out: record.check_out ?? '',
        break_minutes: record.break_minutes.toString(),
        notes: record.notes ?? '',
    });

    const submit = (e: FormEvent) => { e.preventDefault(); form.put(route('admin.hr.attendance.update', record.id)); };

    return (
        <AdminLayout title="Editar Presença" subtitle={record.date}>
            <form onSubmit={submit} className="space-y-4 rounded-2xl border border-slate-200 bg-white p-6">
                <div className="grid gap-4 md:grid-cols-2">
                    <div className="flex flex-col gap-1">
                        <label className="text-xs font-medium text-slate-600">Estado</label>
                        <select value={form.data.status} onChange={(e) => form.setData('status', e.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                            {statuses.map((s) => <option key={s} value={s}>{s}</option>)}
                        </select>
                    </div>
                    <div className="flex flex-col gap-1">
                        <label className="text-xs font-medium text-slate-600">Intervalo (min)</label>
                        <input type="number" value={form.data.break_minutes} onChange={(e) => form.setData('break_minutes', e.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm" min={0} />
                    </div>
                    <div className="flex flex-col gap-1">
                        <label className="text-xs font-medium text-slate-600">Entrada</label>
                        <input type="time" value={form.data.check_in} onChange={(e) => form.setData('check_in', e.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                    </div>
                    <div className="flex flex-col gap-1">
                        <label className="text-xs font-medium text-slate-600">Saída</label>
                        <input type="time" value={form.data.check_out} onChange={(e) => form.setData('check_out', e.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                    </div>
                    <div className="flex flex-col gap-1 md:col-span-2">
                        <label className="text-xs font-medium text-slate-600">Notas</label>
                        <textarea value={form.data.notes} onChange={(e) => form.setData('notes', e.target.value)} className="min-h-20 rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                    </div>
                </div>
                <div className="flex items-center gap-3 pt-2">
                    <button type="submit" disabled={form.processing} className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 disabled:opacity-50">Guardar</button>
                    <Link href={route('admin.hr.attendance.show', record.id)} className="text-sm text-slate-700 hover:text-slate-950">Cancelar</Link>
                </div>
            </form>
        </AdminLayout>
    );
}
