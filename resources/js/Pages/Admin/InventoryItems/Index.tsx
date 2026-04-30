import InventoryFilters from '@/Components/InventoryFilters';
import InventoryItemStatusBadge from '@/Components/InventoryItemStatusBadge';
import InventoryItemTypeBadge from '@/Components/InventoryItemTypeBadge';
import InventoryTabs from '@/Components/InventoryTabs';
import AdminLayout from '@/Layouts/AdminLayout';
import { Link } from '@inertiajs/react';

type Item = { id: number; name: string; sku: string | null; item_type: string; status: string; current_stock: number; minimum_stock: number | null; is_loanable: boolean; is_active: boolean; category?: { name: string } | null; location?: { name: string } | null };

type Props = { items: { data: Item[] }; filters: Record<string, string | boolean | undefined> };

export default function InventoryItemsIndex({ items, filters }: Props) {
    return (
        <AdminLayout title="Recursos Materiais" subtitle="Itens de inventario e stock" headerActions={<Link href={route('admin.inventory-items.create')} className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white">Criar Item</Link>}>
            <InventoryTabs />
            <InventoryFilters indexRouteName="admin.inventory-items.index" initialFilters={filters} />
            <div className="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white">
                <table className="min-w-full divide-y divide-slate-200 text-sm">
                    <thead className="bg-slate-50 text-left text-slate-600"><tr><th className="px-4 py-3">Nome</th><th className="px-4 py-3">SKU</th><th className="px-4 py-3">Categoria</th><th className="px-4 py-3">Localizacao</th><th className="px-4 py-3">Tipo</th><th className="px-4 py-3">Stock</th><th className="px-4 py-3">Minimo</th><th className="px-4 py-3">Estado</th><th className="px-4 py-3">Emprestavel</th><th className="px-4 py-3">Ativo</th></tr></thead>
                    <tbody className="divide-y divide-slate-100">
                        {items.data.map((item) => (
                            <tr key={item.id}>
                                <td className="px-4 py-3"><Link href={route('admin.inventory-items.show', item.id)}>{item.name}</Link></td>
                                <td className="px-4 py-3">{item.sku ?? '-'}</td>
                                <td className="px-4 py-3">{item.category?.name ?? '-'}</td>
                                <td className="px-4 py-3">{item.location?.name ?? '-'}</td>
                                <td className="px-4 py-3"><InventoryItemTypeBadge itemType={item.item_type} /></td>
                                <td className="px-4 py-3">{item.current_stock}</td>
                                <td className="px-4 py-3">{item.minimum_stock ?? '-'}</td>
                                <td className="px-4 py-3"><InventoryItemStatusBadge status={item.status} /></td>
                                <td className="px-4 py-3">{item.is_loanable ? 'Sim' : 'Nao'}</td>
                                <td className="px-4 py-3">{item.is_active ? 'Sim' : 'Nao'}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AdminLayout>
    );
}
