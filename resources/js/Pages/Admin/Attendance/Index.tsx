import AdminLayout from '@/Layouts/AdminLayout';
import { Link, router } from '@inertiajs/react';

type Record = {
    id: number; date: string; status: string; check_in: string | null; check_out: string | null;
    worked_minutes: number | null; validated_at: string | null;
    employee: { id: number; employee_number: string } | null;
};

type EmpOption = { id: number; employee_number: string; role_title: string | null };

type Props = {
    records: { data: Record[]; links: { url: string | null; label: string; active: boolean }[] };
    employees: EmpOption[];
    statuses: string[];
    filters: { employee?: string; date?: string; status?: string; validated?: string };
};

export default function AttendanceIndex({ records, employees, statuses, filters }: Props) {
    const filter = (key: string, value: string) =>
        router.get(route('admin.hr.attendance.index'), { ...filters, [key]: value || undefined }, { preserveState: true, replace: true });

    return (
        <AdminLayout
            title="Presenças"
            subtitle="Registo de assiduidade dos funcionários"
            headerActions={
                <Link href={route('admin.hr.attendance.create')} className="inline-flex rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                    Registar Presença
                </Link>
            }
        >
            <div className="mb-4 flex flex-wrap gap-2 rounded-2xl border border-slate-200 bg-white p-4">
                <select defaultValue={filters.employee ?? ''} onChange={(e) => filter('employee', e.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Todos os funcionários</option>
                    {employees.map((e) => <option key={e.id} value={e.id}>{e.employee_number}</option>)}
                </select>
                <input type="date" defaultValue={filters.date ?? ''} onChange={(e) => filter('date', e.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                <select defaultValue={filters.status ?? ''} onChange={(e) => filter('status', e.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Todos os estados</option>
                    {statuses.map((s) => <option key={s} value={s}>{s}</option>)}
                </select>
                <select defaultValue={filters.validated ?? ''} onChange={(e) => filter('validated', e.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Validado / Não validado</option>
                    <option value="yes">Validado</option>
                    <option value="no">Não validado</option>
                </select>
            </div>

            <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                <table className="min-w-full divide-y divide-slate-200 text-sm">
                    <thead className="bg-slate-50 text-left text-slate-600">
                        <tr>
                            <th className="px-4 py-3">Funcionário</th>
                            <th className="px-4 py-3">Data</th>
                            <th className="px-4 py-3">Estado</th>
                            <th className="px-4 py-3">Entrada</th>
                            <th className="px-4 py-3">Saída</th>
                            <th className="px-4 py-3">Validado</th>
                            <th className="px-4 py-3">Ações</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                        {records.data.length === 0 && <tr><td colSpan={7} className="px-4 py-6 text-center text-slate-500">Nenhum registo encontrado.</td></tr>}
                        {records.data.map((rec) => (
                            <tr key={rec.id}>
                                <td className="px-4 py-3 font-mono text-slate-700">{rec.employee?.employee_number ?? '-'}</td>
                                <td className="px-4 py-3 text-slate-700">{rec.date}</td>
                                <td className="px-4 py-3"><span className="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-700">{rec.status}</span></td>
                                <td className="px-4 py-3 text-slate-700">{rec.check_in ?? '-'}</td>
                                <td className="px-4 py-3 text-slate-700">{rec.check_out ?? '-'}</td>
                                <td className="px-4 py-3">
                                    {rec.validated_at ? <span className="text-green-600 text-xs">Sim</span> : <span className="text-slate-400 text-xs">Não</span>}
                                </td>
                                <td className="px-4 py-3">
                                    <div className="flex gap-3">
                                        <Link href={route('admin.hr.attendance.show', rec.id)} className="text-slate-700 hover:text-slate-950">Ver</Link>
                                        {!rec.validated_at && (
                                            <Link href={route('admin.hr.attendance.validate', rec.id)} method="patch" as="button" className="text-blue-700 hover:text-blue-950">Validar</Link>
                                        )}
                                    </div>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            {records.links.length > 3 && (
                <div className="mt-4 flex gap-1">
                    {records.links.map((link, i) => (
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
