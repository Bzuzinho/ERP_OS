import InventoryTabs from '@/Components/InventoryTabs';
import AdminLayout from '@/Layouts/AdminLayout';
import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type Props = { items: any[]; breakageTypes: string[] };

export default function InventoryBreakagesCreate({ items, breakageTypes }: Props) {
    const form = useForm({ inventory_item_id: '', quantity: '', breakage_type: breakageTypes[0] ?? 'damaged', description: '' });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(route('admin.inventory-breakages.store'));
    };

    return (
        <AdminLayout title="Reportar Quebra" subtitle="Registar perda, dano ou quebra de item">
            <InventoryTabs />
            <form onSubmit={submit} className="space-y-3 rounded-2xl border border-slate-200 bg-white p-4">
                <select value={form.data.inventory_item_id} onChange={(event) => form.setData('inventory_item_id', event.target.value)} className="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"><option value="">Item</option>{items.map((item) => <option key={item.id} value={item.id}>{item.name}</option>)}</select>
                <input type="number" step="0.01" value={form.data.quantity} onChange={(event) => form.setData('quantity', event.target.value)} placeholder="Quantidade" className="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                <select value={form.data.breakage_type} onChange={(event) => form.setData('breakage_type', event.target.value)} className="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">{breakageTypes.map((item) => <option key={item} value={item}>{item}</option>)}</select>
                <textarea value={form.data.description} onChange={(event) => form.setData('description', event.target.value)} placeholder="Descricao" className="min-h-24 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                <button className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white">Guardar</button>
                <Link href={route('admin.inventory-breakages.index')} className="ml-3 text-sm text-slate-700">Cancelar</Link>
            </form>
        </AdminLayout>
    );
}
