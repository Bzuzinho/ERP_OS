import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';

type RequestItem = {
    id: number;
    quantity_requested: string;
    quantity_approved: string | null;
    quantity_delivered: string | null;
    quantity_returned: string | null;
    inventory_item?: { id: number; name: string; unit?: string | null } | null;
};

type Props = {
    request: {
        id: number;
        title: string;
        status: string;
        notes: string | null;
        requested_by?: { id: number; name: string } | null;
        approved_by?: { id: number; name: string } | null;
        rejected_by?: { id: number; name: string } | null;
        items: RequestItem[];
    };
    can: {
        approve: boolean;
        deliver: boolean;
        return: boolean;
        manage?: boolean;
    };
};

export default function ResourceRequestsShow({ request, can }: Props) {
    const approveForm = useForm({ items: {} as Record<number, { quantity_approved: string }> });
    const rejectForm = useForm({ rejection_reason: '' });
    const deliverForm = useForm({ items: {} as Record<number, { quantity_delivered: string }> });
    const returnForm = useForm({ items: {} as Record<number, { quantity_returned: string }> });

    const submitApprove = () => {
        const items = request.items.reduce<Record<number, { quantity_approved: string }>>((acc, item) => {
            acc[item.id] = { quantity_approved: item.quantity_requested };

            return acc;
        }, {});

        approveForm.setData('items', items);
        approveForm.post(route('admin.resource-requests.approve', request.id));
    };

    const submitDeliver = () => {
        const items = request.items.reduce<Record<number, { quantity_delivered: string }>>((acc, item) => {
            acc[item.id] = { quantity_delivered: item.quantity_approved ?? item.quantity_requested };

            return acc;
        }, {});

        deliverForm.setData('items', items);
        deliverForm.post(route('admin.resource-requests.deliver', request.id));
    };

    const submitReturn = () => {
        const items = request.items.reduce<Record<number, { quantity_returned: string }>>((acc, item) => {
            const delivered = item.quantity_delivered ?? '0';
            acc[item.id] = { quantity_returned: delivered };

            return acc;
        }, {});

        returnForm.setData('items', items);
        returnForm.post(route('admin.resource-requests.return', request.id));
    };

    return (
        <AdminLayout title={`Requisicao #${request.id}`} subtitle={request.title}>
            <Head title={`Requisicao #${request.id}`} />

            <section className="rounded-2xl border border-slate-200 bg-white p-4 text-sm text-slate-700">
                <p><span className="font-semibold text-slate-900">Estado:</span> {request.status}</p>
                <p><span className="font-semibold text-slate-900">Solicitante:</span> {request.requested_by?.name ?? '-'}</p>
                <p><span className="font-semibold text-slate-900">Aprovador:</span> {request.approved_by?.name ?? '-'}</p>
                <p><span className="font-semibold text-slate-900">Notas:</span> {request.notes ?? '-'}</p>
            </section>

            <section className="mt-4 rounded-2xl border border-slate-200 bg-white p-4">
                <p className="mb-2 font-semibold text-slate-900">Itens</p>
                <ul className="space-y-2 text-sm text-slate-700">
                    {request.items.map((item) => (
                        <li key={item.id} className="rounded-lg bg-slate-50 px-3 py-2">
                            <p className="font-medium text-slate-900">{item.inventory_item?.name ?? '-'}</p>
                            <p>Requisitado: {item.quantity_requested}</p>
                            <p>Aprovado: {item.quantity_approved ?? '-'}</p>
                            <p>Entregue: {item.quantity_delivered ?? '-'}</p>
                            <p>Devolvido: {item.quantity_returned ?? '-'}</p>
                        </li>
                    ))}
                </ul>
            </section>

            <div className="mt-4 flex flex-wrap gap-2">
                {can.approve ? (
                    <>
                        <button onClick={submitApprove} className="rounded-lg bg-emerald-700 px-3 py-2 text-xs font-medium text-white">Aprovar</button>
                        <button onClick={() => rejectForm.post(route('admin.resource-requests.reject', request.id))} className="rounded-lg bg-rose-700 px-3 py-2 text-xs font-medium text-white">Rejeitar</button>
                    </>
                ) : null}
                {can.manage ? (
                    <button onClick={() => approveForm.post(route('admin.resource-requests.prepare', request.id))} className="rounded-lg bg-amber-700 px-3 py-2 text-xs font-medium text-white">Preparar</button>
                ) : null}
                {can.deliver ? (
                    <button onClick={submitDeliver} className="rounded-lg bg-blue-700 px-3 py-2 text-xs font-medium text-white">Entregar</button>
                ) : null}
                {can.return ? (
                    <button onClick={submitReturn} className="rounded-lg bg-slate-700 px-3 py-2 text-xs font-medium text-white">Registar devolucao</button>
                ) : null}
            </div>
        </AdminLayout>
    );
}
