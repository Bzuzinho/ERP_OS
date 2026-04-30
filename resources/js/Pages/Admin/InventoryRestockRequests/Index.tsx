import InventoryFilters from '@/Components/InventoryFilters';
import InventoryRestockStatusBadge from '@/Components/InventoryRestockStatusBadge';
import InventoryTabs from '@/Components/InventoryTabs';
import AdminLayout from '@/Layouts/AdminLayout';
import { Link } from '@inertiajs/react';

type Props = { restockRequests: { data: any[] }; filters: Record<string, string | boolean | undefined> };

export default function InventoryRestockRequestsIndex({ restockRequests, filters }: Props) {
    return (
        <AdminLayout title="Pedidos de Reposicao" subtitle="Gestao de pedidos de reposicao" headerActions={<Link href={route('admin.inventory-restock-requests.create')} className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white">Novo Pedido</Link>}>
            <InventoryTabs />
            <InventoryFilters indexRouteName="admin.inventory-restock-requests.index" initialFilters={filters} />
            <div className="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white">
                <table className="min-w-full divide-y divide-slate-200 text-sm">
                    <thead className="bg-slate-50 text-left text-slate-600"><tr><th className="px-4 py-3">Item</th><th className="px-4 py-3">Qtd Solicitada</th><th className="px-4 py-3">Qtd Aprovada</th><th className="px-4 py-3">Solicitante</th><th className="px-4 py-3">Estado</th></tr></thead>
                    <tbody className="divide-y divide-slate-100">
                        {restockRequests.data.map((request) => (
                            <tr key={request.id}><td className="px-4 py-3"><Link href={route('admin.inventory-restock-requests.show', request.id)}>{request.item?.name ?? '-'}</Link></td><td className="px-4 py-3">{request.quantity_requested}</td><td className="px-4 py-3">{request.quantity_approved ?? '-'}</td><td className="px-4 py-3">{request.requester?.name ?? '-'}</td><td className="px-4 py-3"><InventoryRestockStatusBadge status={request.status} /></td></tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AdminLayout>
    );
}
