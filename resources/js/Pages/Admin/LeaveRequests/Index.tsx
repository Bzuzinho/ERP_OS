import AdminLayout from '@/Layouts/AdminLayout';
import { Link, router } from '@inertiajs/react';

type LeaveRequest = {
    id: number; leave_type: string; status: string; start_date: string; end_date: string;
    reason: string | null; rejection_reason: string | null; approved_at: string | null;
    employee: { id: number; employee_number: string } | null;
    absence_type: { id: number; name: string } | null;
    approver: { id: number; name: string } | null;
    rejecter: { id: number; name: string } | null;
};

type EmpOption = { id: number; employee_number: string };

type Props = {
    requests: { data: LeaveRequest[]; links: { url: string | null; label: string; active: boolean }[] };
    employees: EmpOption[];
    statuses: string[];
    leaveTypes: string[];
    filters: { status?: string; employee?: string; leave_type?: string };
};

const statusColor: Record<string, string> = {
    requested: 'bg-yellow-100 text-yellow-700',
    approved: 'bg-green-100 text-green-700',
    rejected: 'bg-red-100 text-red-700',
    cancelled: 'bg-slate-100 text-slate-600',
};

export default function LeaveRequestsIndex({ requests, employees, statuses, leaveTypes, filters }: Props) {
    const filter = (key: string, value: string) =>
        router.get(route('admin.hr.leave-requests.index'), { ...filters, [key]: value || undefined }, { preserveState: true, replace: true });

    return (
        <AdminLayout
            title="Pedidos de Ausência"
            subtitle="Gestão de pedidos de férias e licenças"
            headerActions={
                <Link href={route('admin.hr.leave-requests.create')} className="inline-flex rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                    Novo Pedido
                </Link>
            }
        >
            <div className="mb-4 flex flex-wrap gap-2 rounded-2xl border border-slate-200 bg-white p-4">
                <select defaultValue={filters.status ?? ''} onChange={(e) => filter('status', e.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Todos os estados</option>
                    {statuses.map((s) => <option key={s} value={s}>{s}</option>)}
                </select>
                <select defaultValue={filters.employee ?? ''} onChange={(e) => filter('employee', e.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Todos os funcionários</option>
                    {employees.map((e) => <option key={e.id} value={e.id}>{e.employee_number}</option>)}
                </select>
                <select defaultValue={filters.leave_type ?? ''} onChange={(e) => filter('leave_type', e.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Todos os tipos</option>
                    {leaveTypes.map((t) => <option key={t} value={t}>{t}</option>)}
                </select>
            </div>

            <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                <table className="min-w-full divide-y divide-slate-200 text-sm">
                    <thead className="bg-slate-50 text-left text-slate-600">
                        <tr>
                            <th className="px-4 py-3">Funcionário</th>
                            <th className="px-4 py-3">Tipo</th>
                            <th className="px-4 py-3">Período</th>
                            <th className="px-4 py-3">Estado</th>
                            <th className="px-4 py-3">Ações</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                        {requests.data.length === 0 && <tr><td colSpan={5} className="px-4 py-6 text-center text-slate-500">Nenhum pedido encontrado.</td></tr>}
                        {requests.data.map((req) => (
                            <tr key={req.id}>
                                <td className="px-4 py-3 font-mono text-slate-700">{req.employee?.employee_number ?? '-'}</td>
                                <td className="px-4 py-3 text-slate-700">{req.absence_type?.name ?? req.leave_type}</td>
                                <td className="px-4 py-3 text-slate-700">{req.start_date} → {req.end_date}</td>
                                <td className="px-4 py-3">
                                    <span className={`inline-flex rounded-full px-2 py-0.5 text-xs font-medium ${statusColor[req.status] ?? 'bg-slate-100 text-slate-600'}`}>
                                        {req.status}
                                    </span>
                                </td>
                                <td className="px-4 py-3">
                                    <Link href={route('admin.hr.leave-requests.show', req.id)} className="text-slate-700 hover:text-slate-950">Ver</Link>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AdminLayout>
    );
}
