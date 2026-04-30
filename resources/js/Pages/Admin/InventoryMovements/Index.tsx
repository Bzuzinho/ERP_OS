import InventoryFilters from '@/Components/InventoryFilters';
import InventoryMovementTypeBadge from '@/Components/InventoryMovementTypeBadge';
import InventoryTabs from '@/Components/InventoryTabs';
import AdminLayout from '@/Layouts/AdminLayout';
import { Link } from '@inertiajs/react';

type Props = { movements: { data: any[] }; filters: Record<string, string | boolean | undefined> };

export default function InventoryMovementsIndex({ movements, filters }: Props) {
    return (
        <AdminLayout title="Movimentos de Inventario" subtitle="Registo de entradas, saidas e transferencias" headerActions={<Link href={route('admin.inventory-movements.create')} className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white">Registar Movimento</Link>}>
            <InventoryTabs />
            <InventoryFilters indexRouteName="admin.inventory-movements.index" initialFilters={filters} />
            <div className="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white">
                <table className="min-w-full divide-y divide-slate-200 text-sm">
                    <thead className="bg-slate-50 text-left text-slate-600"><tr><th className="px-4 py-3">Item</th><th className="px-4 py-3">Tipo</th><th className="px-4 py-3">Quantidade</th><th className="px-4 py-3">Origem</th><th className="px-4 py-3">Destino</th><th className="px-4 py-3">Responsavel</th><th className="px-4 py-3">Data</th></tr></thead>
                    <tbody className="divide-y divide-slate-100">
                        {movements.data.map((movement) => (
                            <tr key={movement.id}><td className="px-4 py-3"><Link href={route('admin.inventory-movements.show', movement.id)}>{movement.item?.name ?? '-'}</Link></td><td className="px-4 py-3"><InventoryMovementTypeBadge movementType={movement.movement_type} /></td><td className="px-4 py-3">{movement.quantity}</td><td className="px-4 py-3">{movement.from_location?.name ?? '-'}</td><td className="px-4 py-3">{movement.to_location?.name ?? '-'}</td><td className="px-4 py-3">{movement.handled_by?.name ?? '-'}</td><td className="px-4 py-3">{movement.occurred_at ? new Date(movement.occurred_at).toLocaleString() : '-'}</td></tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AdminLayout>
    );
}
