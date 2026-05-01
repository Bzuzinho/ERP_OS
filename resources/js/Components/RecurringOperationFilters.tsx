type Props = {
    filters: Record<string, string | null | undefined>;
    statuses: string[];
    frequencies: string[];
    types: string[];
    onChange: (name: string, value: string) => void;
};

export default function RecurringOperationFilters({ filters, statuses, frequencies, types, onChange }: Props) {
    return (
        <div className="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 md:grid-cols-3 xl:grid-cols-4">
            <select value={filters.status ?? ''} onChange={(e) => onChange('status', e.target.value)} className="rounded-lg border-slate-300">
                <option value="">Estado</option>
                {statuses.map((status) => <option key={status} value={status}>{status}</option>)}
            </select>
            <select value={filters.frequency ?? ''} onChange={(e) => onChange('frequency', e.target.value)} className="rounded-lg border-slate-300">
                <option value="">Frequência</option>
                {frequencies.map((frequency) => <option key={frequency} value={frequency}>{frequency}</option>)}
            </select>
            <select value={filters.operation_type ?? ''} onChange={(e) => onChange('operation_type', e.target.value)} className="rounded-lg border-slate-300">
                <option value="">Tipo</option>
                {types.map((type) => <option key={type} value={type}>{type}</option>)}
            </select>
            <input type="text" value={filters.owner_user_id ?? ''} onChange={(e) => onChange('owner_user_id', e.target.value)} placeholder="Responsável (ID)" className="rounded-lg border-slate-300" />
        </div>
    );
}
