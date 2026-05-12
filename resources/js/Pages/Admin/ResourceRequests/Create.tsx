import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';

type InventoryItem = {
    id: number;
    name: string;
    item_type: string;
    is_stock_tracked: boolean;
    is_loanable: boolean;
    current_stock: string;
};

type Props = {
    organizationId: number;
    inventoryItems: InventoryItem[];
};

export default function ResourceRequestsCreate({ organizationId, inventoryItems }: Props) {
    const form = useForm({
        organization_id: organizationId,
        title: '',
        purpose: '',
        needed_from: '',
        needed_until: '',
        requestable_type: '',
        requestable_id: '',
        notes: '',
        items: [{ inventory_item_id: '', quantity_requested: '1' }],
    });

    const submit = () => {
        form.post(route('admin.resource-requests.store'));
    };

    return (
        <AdminLayout title="Nova Requisicao de Recursos" subtitle="Definir materiais e quantidades para operacao.">
            <Head title="Nova Requisicao de Recursos" />

            <section className="rounded-2xl border border-slate-200 bg-white p-4">
                <div className="grid gap-4 md:grid-cols-2">
                    <div>
                        <label className="mb-1 block text-xs font-semibold text-slate-700">Titulo</label>
                        <input value={form.data.title} onChange={(e) => form.setData('title', e.target.value)} className="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                    </div>
                    <div>
                        <label className="mb-1 block text-xs font-semibold text-slate-700">Observacoes</label>
                        <input value={form.data.notes} onChange={(e) => form.setData('notes', e.target.value)} className="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                    </div>
                </div>

                <div className="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <p className="mb-2 text-sm font-semibold text-slate-900">Itens</p>
                    {form.data.items.map((item, idx) => (
                        <div key={idx} className="mb-2 grid gap-2 md:grid-cols-2">
                            <select
                                value={item.inventory_item_id}
                                onChange={(e) => {
                                    const next = [...form.data.items];
                                    next[idx].inventory_item_id = e.target.value;
                                    form.setData('items', next);
                                }}
                                className="rounded-lg border border-slate-300 px-3 py-2 text-sm"
                            >
                                <option value="">Selecionar item</option>
                                {inventoryItems.map((inventoryItem) => (
                                    <option key={inventoryItem.id} value={inventoryItem.id}>{inventoryItem.name}</option>
                                ))}
                            </select>
                            <input
                                type="number"
                                min="0.01"
                                step="0.01"
                                value={item.quantity_requested}
                                onChange={(e) => {
                                    const next = [...form.data.items];
                                    next[idx].quantity_requested = e.target.value;
                                    form.setData('items', next);
                                }}
                                className="rounded-lg border border-slate-300 px-3 py-2 text-sm"
                            />
                        </div>
                    ))}
                </div>

                <button onClick={submit} className="mt-4 rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                    Criar requisicao
                </button>
            </section>
        </AdminLayout>
    );
}
