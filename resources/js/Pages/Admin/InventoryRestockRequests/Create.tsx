import InventoryTabs from '@/Components/InventoryTabs';
import AdminLayout from '@/Layouts/AdminLayout';
import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type Props = { items: { id: number; name: string; sku: string | null }[]; suggestions: Record<number, number> };

export default function InventoryRestockRequestsCreate({ items, suggestions }: Props) {
    const form = useForm({ inventory_item_id: '', quantity_requested: '', reason: '', notes: '' });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(route('admin.inventory-restock-requests.store'));
    };

    return (
        <AdminLayout title="Novo Pedido de Reposicao" subtitle="Criar pedido de reposicao de stock">
            <InventoryTabs />
            <form onSubmit={submit} className="space-y-3 rounded-2xl border border-slate-200 bg-white p-4">
                <select value={form.data.inventory_item_id} onChange={(event) => {
                    const id = event.target.value;
                    form.setData('inventory_item_id', id);
                    const suggestion = suggestions[Number(id)];
                    if (suggestion) {
                        form.setData('quantity_requested', String(suggestion));
                    }
                }} className="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"><option value="">Item</option>{items.map((item) => <option key={item.id} value={item.id}>{item.name} {item.sku ? `(${item.sku})` : ''}</option>)}</select>
                <input type="number" step="0.01" value={form.data.quantity_requested} onChange={(event) => form.setData('quantity_requested', event.target.value)} placeholder="Quantidade solicitada" className="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                <textarea value={form.data.reason} onChange={(event) => form.setData('reason', event.target.value)} placeholder="Motivo" className="min-h-24 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                <button className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white">Guardar</button>
                <Link href={route('admin.inventory-restock-requests.index')} className="ml-3 text-sm text-slate-700">Cancelar</Link>
            </form>
        </AdminLayout>
    );
}
