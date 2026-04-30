import InventoryRestockStatusBadge from '@/Components/InventoryRestockStatusBadge';
import InventoryTabs from '@/Components/InventoryTabs';
import AdminLayout from '@/Layouts/AdminLayout';
import { useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type Props = { restockRequest: any; can: { approve: boolean; reject: boolean; complete: boolean } };

export default function InventoryRestockRequestsShow({ restockRequest, can }: Props) {
    const approve = useForm({ quantity_approved: restockRequest.quantity_requested, notes: '' });
    const reject = useForm({ rejection_reason: '' });
    const complete = useForm({ notes: '' });

    const submitApprove = (event: FormEvent) => { event.preventDefault(); approve.post(route('admin.inventory-restock-requests.approve', restockRequest.id)); };
    const submitReject = (event: FormEvent) => { event.preventDefault(); reject.post(route('admin.inventory-restock-requests.reject', restockRequest.id)); };
    const submitComplete = (event: FormEvent) => { event.preventDefault(); complete.post(route('admin.inventory-restock-requests.complete', restockRequest.id)); };

    return (
        <AdminLayout title="Detalhe de Reposicao" subtitle="Aprovar, rejeitar ou completar pedido de reposicao">
            <InventoryTabs />
            <div className="rounded-2xl border border-slate-200 bg-white p-4 text-sm text-slate-700">
                <p><strong>Item:</strong> {restockRequest.item?.name ?? '-'}</p>
                <p><strong>Quantidade solicitada:</strong> {restockRequest.quantity_requested}</p>
                <p><strong>Estado:</strong> <InventoryRestockStatusBadge status={restockRequest.status} /></p>
            </div>
            <div className="mt-4 grid gap-3 md:grid-cols-3">
                {can.approve ? <form onSubmit={submitApprove} className="rounded-2xl border border-slate-200 bg-white p-4"><input type="number" step="0.01" value={approve.data.quantity_approved} onChange={(event) => approve.setData('quantity_approved', event.target.value)} className="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" /><button className="mt-2 rounded-xl bg-emerald-700 px-3 py-2 text-sm font-medium text-white">Aprovar</button></form> : null}
                {can.reject ? <form onSubmit={submitReject} className="rounded-2xl border border-slate-200 bg-white p-4"><textarea value={reject.data.rejection_reason} onChange={(event) => reject.setData('rejection_reason', event.target.value)} placeholder="Motivo rejeicao" className="min-h-20 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" /><button className="mt-2 rounded-xl bg-rose-700 px-3 py-2 text-sm font-medium text-white">Rejeitar</button></form> : null}
                {can.complete ? <form onSubmit={submitComplete} className="rounded-2xl border border-slate-200 bg-white p-4"><textarea value={complete.data.notes} onChange={(event) => complete.setData('notes', event.target.value)} placeholder="Notas" className="min-h-20 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" /><button className="mt-2 rounded-xl bg-slate-900 px-3 py-2 text-sm font-medium text-white">Completar</button></form> : null}
            </div>
        </AdminLayout>
    );
}
