import AdminLayout from '@/Layouts/AdminLayout';
import { Link, router, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type LeaveRequest = {
    id: number; leave_type: string; status: string; start_date: string; end_date: string;
    reason: string | null; rejection_reason: string | null; approved_at: string | null;
    employee: { id: number; employee_number: string } | null;
    absence_type: { id: number; name: string } | null;
    approver: { id: number; name: string } | null;
    rejecter: { id: number; name: string } | null;
};

type Props = { request: LeaveRequest };

const statusColor: Record<string, string> = {
    requested: 'bg-yellow-100 text-yellow-700',
    approved: 'bg-green-100 text-green-700',
    rejected: 'bg-red-100 text-red-700',
    cancelled: 'bg-slate-100 text-slate-600',
};

export default function LeaveRequestsShow({ request }: Props) {
    const rejectForm = useForm({ rejection_reason: '' });

    const approve = () => {
        if (confirm('Aprovar este pedido de ausência?')) {
            router.post(route('admin.hr.leave-requests.approve', request.id));
        }
    };

    const submitReject = (e: FormEvent) => {
        e.preventDefault();
        rejectForm.post(route('admin.hr.leave-requests.reject', request.id));
    };

    const cancel = () => {
        if (confirm('Cancelar este pedido?')) {
            router.post(route('admin.hr.leave-requests.cancel', request.id), { cancellation_reason: 'Cancelado pelo utilizador' });
        }
    };

    return (
        <AdminLayout
            title="Pedido de Ausência"
            subtitle={`${request.employee?.employee_number ?? '-'} — ${request.start_date} a ${request.end_date}`}
            headerActions={
                <div className="flex gap-2">
                    {request.status === 'requested' && (
                        <>
                            <button onClick={approve} className="rounded-xl bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800">Aprovar</button>
                        </>
                    )}
                    {(request.status === 'requested' || request.status === 'approved') && (
                        <button onClick={cancel} className="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancelar</button>
                    )}
                </div>
            }
        >
            <div className="grid gap-4 lg:grid-cols-3">
                <div className="lg:col-span-2 space-y-4">
                    <div className="rounded-2xl border border-slate-200 bg-white p-6">
                        <h2 className="mb-3 text-sm font-semibold text-slate-900">Detalhes</h2>
                        <dl className="grid gap-2 text-sm md:grid-cols-2">
                            <div className="flex gap-2"><dt className="w-28 text-slate-500">Funcionário:</dt><dd>{request.employee?.employee_number ?? '-'}</dd></div>
                            <div className="flex gap-2"><dt className="w-28 text-slate-500">Tipo:</dt><dd>{request.absence_type?.name ?? request.leave_type}</dd></div>
                            <div className="flex gap-2"><dt className="w-28 text-slate-500">Início:</dt><dd>{request.start_date}</dd></div>
                            <div className="flex gap-2"><dt className="w-28 text-slate-500">Fim:</dt><dd>{request.end_date}</dd></div>
                            <div className="flex gap-2"><dt className="w-28 text-slate-500">Estado:</dt>
                                <dd><span className={`rounded-full px-2 py-0.5 text-xs font-medium ${statusColor[request.status] ?? ''}`}>{request.status}</span></dd>
                            </div>
                            {request.approver && <div className="flex gap-2"><dt className="w-28 text-slate-500">Aprovado por:</dt><dd>{request.approver.name}</dd></div>}
                            {request.rejecter && <div className="flex gap-2"><dt className="w-28 text-slate-500">Rejeitado por:</dt><dd>{request.rejecter.name}</dd></div>}
                            {request.reason && <div className="flex gap-2 md:col-span-2"><dt className="w-28 text-slate-500">Motivo:</dt><dd>{request.reason}</dd></div>}
                            {request.rejection_reason && <div className="flex gap-2 md:col-span-2"><dt className="w-28 text-slate-500">Rejeição:</dt><dd className="text-red-700">{request.rejection_reason}</dd></div>}
                        </dl>
                    </div>
                </div>

                {request.status === 'requested' && (
                    <div className="rounded-2xl border border-slate-200 bg-white p-6">
                        <h2 className="mb-3 text-sm font-semibold text-slate-900">Rejeitar</h2>
                        <form onSubmit={submitReject} className="space-y-3">
                            <textarea
                                value={rejectForm.data.rejection_reason}
                                onChange={(e) => rejectForm.setData('rejection_reason', e.target.value)}
                                placeholder="Motivo da rejeição *"
                                className="min-h-24 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"
                                required
                            />
                            <button type="submit" disabled={rejectForm.processing} className="w-full rounded-xl bg-red-700 px-4 py-2 text-sm font-medium text-white hover:bg-red-800 disabled:opacity-50">
                                Rejeitar Pedido
                            </button>
                        </form>
                    </div>
                )}
            </div>

            <div className="mt-4">
                <Link href={route('admin.hr.leave-requests.index')} className="text-sm text-slate-600 hover:text-slate-950">← Voltar</Link>
            </div>
        </AdminLayout>
    );
}
