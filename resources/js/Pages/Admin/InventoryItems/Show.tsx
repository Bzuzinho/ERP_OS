import InventoryMovementTimeline from '@/Components/InventoryMovementTimeline';
import InventoryStockCard from '@/Components/InventoryStockCard';
import InventoryStockStatusBadge from '@/Components/InventoryStockStatusBadge';
import InventoryTabs from '@/Components/InventoryTabs';
import AdminLayout from '@/Layouts/AdminLayout';
import { Link } from '@inertiajs/react';

type Props = { item: any; stockStatus: string; can: { move: boolean; loan: boolean; breakage: boolean; restock: boolean } };

export default function InventoryItemsShow({ item, stockStatus, can }: Props) {
    return (
        <AdminLayout title={item.name} subtitle="Detalhe do item de inventario" headerActions={<div className="flex gap-2">{can.move ? <Link href={route('admin.inventory-movements.create')} className="rounded-xl bg-slate-900 px-3 py-2 text-sm text-white">Entrada/Saida</Link> : null}{can.loan ? <Link href={route('admin.inventory-loans.create')} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">Emprestimo</Link> : null}{can.breakage ? <Link href={route('admin.inventory-breakages.create')} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">Quebra</Link> : null}{can.restock ? <Link href={route('admin.inventory-restock-requests.create')} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">Reposicao</Link> : null}</div>}>
            <InventoryTabs />
            <div className="grid gap-4 md:grid-cols-4">
                <InventoryStockCard title="Stock Atual" value={item.current_stock} />
                <InventoryStockCard title="Stock Minimo" value={item.minimum_stock ?? '-'} />
                <InventoryStockCard title="Stock Maximo" value={item.maximum_stock ?? '-'} />
                <section className="rounded-2xl border border-slate-200 bg-white p-4"><p className="text-sm text-slate-500">Estado de Stock</p><div className="mt-2"><InventoryStockStatusBadge status={stockStatus} /></div></section>
            </div>
            <div className="mt-4 rounded-2xl border border-slate-200 bg-white p-4">
                <h2 className="text-lg font-semibold text-slate-900">Movimentos Recentes</h2>
                <div className="mt-3"><InventoryMovementTimeline movements={item.movements ?? []} /></div>
            </div>
        </AdminLayout>
    );
}
