type Resource = {
    id: number;
    quantity?: number | null;
    notes?: string | null;
    inventory_item?: { id: number; name: string; sku?: string | null } | null;
    space?: { id: number; name: string } | null;
};

type Props = { resources: Resource[] };

export default function OperationalPlanResourcesList({ resources }: Props) {
    return (
        <ul className="space-y-2 text-sm">
            {resources.map((resource) => (
                <li key={resource.id} className="rounded-xl bg-slate-50 px-3 py-2 text-slate-700">
                    {resource.inventory_item?.name ?? resource.space?.name ?? 'Recurso'}
                    {resource.quantity ? ` • ${resource.quantity}` : ''}
                    {resource.notes ? ` • ${resource.notes}` : ''}
                </li>
            ))}
            {resources.length === 0 ? <li className="text-slate-500">Sem recursos associados.</li> : null}
        </ul>
    );
}
