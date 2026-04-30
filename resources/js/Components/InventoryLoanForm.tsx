type Props = {
    data: Record<string, string | number>;
    setData: (key: string, value: string | number) => void;
    items: { id: number; name: string; sku?: string | null; current_stock?: number }[];
    users: { id: number; name: string }[];
    contacts: { id: number; name: string }[];
};

export default function InventoryLoanForm({ data, setData, items, users, contacts }: Props) {
    return (
        <div className="grid gap-3 md:grid-cols-2">
            <select value={String(data.inventory_item_id ?? '')} onChange={(event) => setData('inventory_item_id', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                <option value="">Item</option>
                {items.map((item) => <option key={item.id} value={item.id}>{item.name} ({item.current_stock ?? 0})</option>)}
            </select>
            <input type="number" step="0.01" value={String(data.quantity ?? '')} onChange={(event) => setData('quantity', event.target.value)} placeholder="Quantidade" className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
            <select value={String(data.borrower_user_id ?? '')} onChange={(event) => setData('borrower_user_id', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                <option value="">Utilizador</option>
                {users.map((item) => <option key={item.id} value={item.id}>{item.name}</option>)}
            </select>
            <select value={String(data.borrower_contact_id ?? '')} onChange={(event) => setData('borrower_contact_id', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                <option value="">Contacto</option>
                {contacts.map((item) => <option key={item.id} value={item.id}>{item.name}</option>)}
            </select>
            <input type="datetime-local" value={String(data.expected_return_at ?? '')} onChange={(event) => setData('expected_return_at', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
            <textarea value={String(data.notes ?? '')} onChange={(event) => setData('notes', event.target.value)} placeholder="Notas" className="min-h-24 rounded-xl border border-slate-300 px-3 py-2 text-sm md:col-span-2" />
        </div>
    );
}
