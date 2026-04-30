type RelatedEntity = {
    id: number;
    title?: string;
    name?: string;
    reference?: string;
};

type RelatedType = {
    label: string;
    value: string;
};

type Props = {
    types: RelatedType[];
    selectedType: string;
    selectedId: string;
    onTypeChange: (value: string) => void;
    onIdChange: (value: string) => void;
    entities: {
        tickets: RelatedEntity[];
        contacts: RelatedEntity[];
        tasks: RelatedEntity[];
        events: RelatedEntity[];
    };
};

export default function RelatedEntitySelector({ types, selectedType, selectedId, onTypeChange, onIdChange, entities }: Props) {
    const options = selectedType === 'App\\Models\\Ticket'
        ? entities.tickets
        : selectedType === 'App\\Models\\Contact'
            ? entities.contacts
            : selectedType === 'App\\Models\\Task'
                ? entities.tasks
                : selectedType === 'App\\Models\\Event'
                    ? entities.events
                    : [];

    return (
        <div className="grid gap-3 md:grid-cols-2">
            <select value={selectedType} onChange={(event) => onTypeChange(event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                <option value="">Sem associacao</option>
                {types.map((type) => <option key={type.value} value={type.value}>{type.label}</option>)}
            </select>
            <select value={selectedId} onChange={(event) => onIdChange(event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                <option value="">Selecionar entidade</option>
                {options.map((item) => (
                    <option key={item.id} value={item.id}>
                        {item.reference ? `${item.reference} - ` : ''}{item.title ?? item.name ?? `#${item.id}`}
                    </option>
                ))}
            </select>
        </div>
    );
}
