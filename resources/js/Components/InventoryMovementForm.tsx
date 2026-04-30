type Props = {
    data: Record<string, string | number>;
    setData: (key: string, value: string | number) => void;
    items: { id: number; name: string; sku?: string | null }[];
    movementTypes: string[];
    locations: { id: number; name: string }[];
};

export default function InventoryMovementForm({ data, setData, items, movementTypes, locations }: Props) {
    return (
        <div className="grid gap-3 md:grid-cols-2">
            <select value={String(data.inventory_item_id ?? '')} onChange={(event) => setData('inventory_item_id', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                <option value="">Item</option>
                {items.map((item) => <option key={item.id} value={item.id}>{item.name} {item.sku ? `(${item.sku})` : ''}</option>)}
            </select>
            <select value={String(data.movement_type ?? '')} onChange={(event) => setData('movement_type', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                {movementTypes.map((item) => <option key={item} value={item}>{item}</option>)}
            </select>
            <input type="number" step="0.01" value={String(data.quantity ?? '')} onChange={(event) => setData('quantity', event.target.value)} placeholder="Quantidade" className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
            <input type="number" step="0.01" value={String(data.unit_cost ?? '')} onChange={(event) => setData('unit_cost', event.target.value)} placeholder="Custo unitario" className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
            <select value={String(data.from_location_id ?? '')} onChange={(event) => setData('from_location_id', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                <option value="">Origem</option>
                {locations.map((item) => <option key={item.id} value={item.id}>{item.name}</option>)}
            </select>
            <select value={String(data.to_location_id ?? '')} onChange={(event) => setData('to_location_id', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                <option value="">Destino</option>
                {locations.map((item) => <option key={item.id} value={item.id}>{item.name}</option>)}
            </select>
            <textarea value={String(data.notes ?? '')} onChange={(event) => setData('notes', event.target.value)} placeholder="Notas" className="min-h-24 rounded-xl border border-slate-300 px-3 py-2 text-sm md:col-span-2" />
        </div>
    );
}
