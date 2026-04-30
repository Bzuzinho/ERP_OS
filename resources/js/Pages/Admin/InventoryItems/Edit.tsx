import InventoryItemForm from '@/Components/InventoryItemForm';
import InventoryTabs from '@/Components/InventoryTabs';
import AdminLayout from '@/Layouts/AdminLayout';
import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type Props = { item: any; categories: { id: number; name: string }[]; locations: { id: number; name: string }[]; itemTypes: string[]; units: string[]; statuses: string[] };

export default function InventoryItemsEdit({ item, categories, locations, itemTypes, units, statuses }: Props) {
    const form = useForm({ name: item.name ?? '', slug: item.slug ?? '', inventory_category_id: item.inventory_category_id ? String(item.inventory_category_id) : '', inventory_location_id: item.inventory_location_id ? String(item.inventory_location_id) : '', description: item.description ?? '', sku: item.sku ?? '', item_type: item.item_type ?? itemTypes[0] ?? 'consumable', unit: item.unit ?? units[0] ?? 'unit', minimum_stock: item.minimum_stock ?? '', maximum_stock: item.maximum_stock ?? '', unit_cost: item.unit_cost ?? '', status: item.status ?? statuses[0] ?? 'active', is_stock_tracked: Boolean(item.is_stock_tracked), is_loanable: Boolean(item.is_loanable), is_active: Boolean(item.is_active) });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.put(route('admin.inventory-items.update', item.id));
    };

    return (
        <AdminLayout title="Editar Item" subtitle="Atualizar item de inventario">
            <InventoryTabs />
            <form onSubmit={submit} className="space-y-4 rounded-2xl border border-slate-200 bg-white p-4">
                <InventoryItemForm data={form.data} setData={form.setData} categories={categories} locations={locations} itemTypes={itemTypes} units={units} statuses={statuses} />
                <button className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white">Guardar</button>
                <Link href={route('admin.inventory-items.show', item.id)} className="ml-3 text-sm text-slate-700">Cancelar</Link>
            </form>
        </AdminLayout>
    );
}
