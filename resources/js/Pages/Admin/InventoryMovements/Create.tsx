import InventoryMovementForm from '@/Components/InventoryMovementForm';
import InventoryTabs from '@/Components/InventoryTabs';
import AdminLayout from '@/Layouts/AdminLayout';
import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type Props = { items: any[]; locations: any[]; movementTypes: string[] };

export default function InventoryMovementsCreate({ items, locations, movementTypes }: Props) {
    const form = useForm({ inventory_item_id: '', movement_type: movementTypes[0] ?? 'entry', quantity: '', unit_cost: '', from_location_id: '', to_location_id: '', notes: '' });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(route('admin.inventory-movements.store'));
    };

    return (
        <AdminLayout title="Registar Movimento" subtitle="Entrada, saida, consumo, ajuste ou transferencia">
            <InventoryTabs />
            <form onSubmit={submit} className="space-y-4 rounded-2xl border border-slate-200 bg-white p-4">
                <InventoryMovementForm data={form.data} setData={form.setData} items={items} movementTypes={movementTypes} locations={locations} />
                <button className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white">Guardar</button>
                <Link href={route('admin.inventory-movements.index')} className="ml-3 text-sm text-slate-700">Cancelar</Link>
            </form>
        </AdminLayout>
    );
}
