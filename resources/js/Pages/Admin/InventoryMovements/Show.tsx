import InventoryTabs from '@/Components/InventoryTabs';
import AdminLayout from '@/Layouts/AdminLayout';

type Props = { movement: any };

export default function InventoryMovementsShow({ movement }: Props) {
    return (
        <AdminLayout title="Detalhe de Movimento" subtitle="Informacao do movimento de stock">
            <InventoryTabs />
            <div className="rounded-2xl border border-slate-200 bg-white p-4 text-sm text-slate-700">
                <p><strong>Item:</strong> {movement.item?.name ?? '-'}</p>
                <p><strong>Tipo:</strong> {movement.movement_type}</p>
                <p><strong>Quantidade:</strong> {movement.quantity}</p>
                <p><strong>Origem:</strong> {movement.from_location?.name ?? '-'}</p>
                <p><strong>Destino:</strong> {movement.to_location?.name ?? '-'}</p>
                <p><strong>Data:</strong> {movement.occurred_at ? new Date(movement.occurred_at).toLocaleString() : '-'}</p>
                <p><strong>Notas:</strong> {movement.notes ?? '-'}</p>
            </div>
        </AdminLayout>
    );
}
