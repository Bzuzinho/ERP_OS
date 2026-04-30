import InventoryBreakageStatusBadge from '@/Components/InventoryBreakageStatusBadge';
import InventoryTabs from '@/Components/InventoryTabs';
import AdminLayout from '@/Layouts/AdminLayout';
import { useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type Props = { breakage: any; canResolve: boolean; statuses: string[] };

export default function InventoryBreakagesShow({ breakage, canResolve }: Props) {
    const form = useForm({ status: 'resolved', resolution_notes: '' });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(route('admin.inventory-breakages.resolve', breakage.id));
    };

    return (
        <AdminLayout title="Detalhe de Quebra" subtitle="Acompanhar resolucao de quebra">
            <InventoryTabs />
            <div className="space-y-4">
                <div className="rounded-2xl border border-slate-200 bg-white p-4 text-sm text-slate-700">
                    <p><strong>Item:</strong> {breakage.item?.name ?? '-'}</p>
                    <p><strong>Quantidade:</strong> {breakage.quantity}</p>
                    <p><strong>Tipo:</strong> {breakage.breakage_type}</p>
                    <p><strong>Estado:</strong> <InventoryBreakageStatusBadge status={breakage.status} /></p>
                    <p><strong>Descricao:</strong> {breakage.description ?? '-'}</p>
                </div>
                {canResolve ? (
                    <form onSubmit={submit} className="rounded-2xl border border-slate-200 bg-white p-4">
                        <select value={form.data.status} onChange={(event) => form.setData('status', event.target.value)} className="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"><option value="resolved">resolved</option><option value="written_off">written_off</option><option value="cancelled">cancelled</option></select>
                        <textarea value={form.data.resolution_notes} onChange={(event) => form.setData('resolution_notes', event.target.value)} placeholder="Notas de resolucao" className="mt-3 min-h-20 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                        <button className="mt-3 rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white">Resolver</button>
                    </form>
                ) : null}
            </div>
        </AdminLayout>
    );
}
