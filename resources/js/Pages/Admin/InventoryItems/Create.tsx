import InventoryItemForm from '@/Components/InventoryItemForm';
import InventoryTabs from '@/Components/InventoryTabs';
import AdminLayout from '@/Layouts/AdminLayout';
import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type Props = { categories: { id: number; name: string }[]; locations: { id: number; name: string }[]; itemTypes: string[]; units: string[]; statuses: string[] };

export default function InventoryItemsCreate({ categories, locations, itemTypes, units, statuses }: Props) {
    const form = useForm({ name: '', slug: '', inventory_category_id: '', inventory_location_id: '', description: '', sku: '', item_type: itemTypes[0] ?? 'consumable', unit: units[0] ?? 'unit', current_stock: '0', minimum_stock: '', maximum_stock: '', unit_cost: '', status: statuses[0] ?? 'active', is_stock_tracked: true, is_loanable: false, is_active: true });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(route('admin.inventory-items.store'));
    };

    return (
        <AdminLayout title="Criar Item" subtitle="Novo item de inventario">
            <InventoryTabs />
            <form onSubmit={submit} className="space-y-4 rounded-2xl border border-slate-200 bg-white p-4">
                <InventoryItemForm data={form.data} setData={form.setData} categories={categories} locations={locations} itemTypes={itemTypes} units={units} statuses={statuses} />
                <button className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white">Guardar</button>
                <Link href={route('admin.inventory-items.index')} className="ml-3 text-sm text-slate-700">Cancelar</Link>
            </form>
        </AdminLayout>
    );
}
