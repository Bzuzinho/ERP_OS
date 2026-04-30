type Props = {
    data: Record<string, string | number | boolean>;
    setData: (key: string, value: string | number | boolean) => void;
    categories: { id: number; name: string }[];
    locations: { id: number; name: string }[];
    itemTypes: string[];
    units: string[];
    statuses: string[];
};

export default function InventoryItemForm({ data, setData, categories, locations, itemTypes, units, statuses }: Props) {
    return (
        <div className="grid gap-3 md:grid-cols-2">
            <input value={String(data.name ?? '')} onChange={(event) => setData('name', event.target.value)} placeholder="Nome" className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
            <input value={String(data.sku ?? '')} onChange={(event) => setData('sku', event.target.value)} placeholder="SKU" className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
            <select value={String(data.inventory_category_id ?? '')} onChange={(event) => setData('inventory_category_id', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                <option value="">Categoria</option>
                {categories.map((item) => <option key={item.id} value={item.id}>{item.name}</option>)}
            </select>
            <select value={String(data.inventory_location_id ?? '')} onChange={(event) => setData('inventory_location_id', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                <option value="">Localizacao</option>
                {locations.map((item) => <option key={item.id} value={item.id}>{item.name}</option>)}
            </select>
            <select value={String(data.item_type ?? '')} onChange={(event) => setData('item_type', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                {itemTypes.map((item) => <option key={item} value={item}>{item}</option>)}
            </select>
            <select value={String(data.unit ?? '')} onChange={(event) => setData('unit', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                {units.map((item) => <option key={item} value={item}>{item}</option>)}
            </select>
            <input type="number" step="0.01" value={String(data.current_stock ?? '0')} onChange={(event) => setData('current_stock', event.target.value)} placeholder="Stock atual" className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
            <input type="number" step="0.01" value={String(data.minimum_stock ?? '')} onChange={(event) => setData('minimum_stock', event.target.value)} placeholder="Stock minimo" className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
            <input type="number" step="0.01" value={String(data.maximum_stock ?? '')} onChange={(event) => setData('maximum_stock', event.target.value)} placeholder="Stock maximo" className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
            <input type="number" step="0.01" value={String(data.unit_cost ?? '')} onChange={(event) => setData('unit_cost', event.target.value)} placeholder="Custo unitario" className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
            <select value={String(data.status ?? '')} onChange={(event) => setData('status', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                {statuses.map((item) => <option key={item} value={item}>{item}</option>)}
            </select>
            <label className="flex items-center gap-2 text-sm"><input type="checkbox" checked={Boolean(data.is_stock_tracked)} onChange={(event) => setData('is_stock_tracked', event.target.checked)} />Controla stock</label>
            <label className="flex items-center gap-2 text-sm"><input type="checkbox" checked={Boolean(data.is_loanable)} onChange={(event) => setData('is_loanable', event.target.checked)} />Emprestavel</label>
        </div>
    );
}
