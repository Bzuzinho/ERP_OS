type Item = { id: number; movement_type: string; quantity: number; occurred_at?: string | null; notes?: string | null };

type Props = { movements: Item[] };

export default function InventoryMovementTimeline({ movements }: Props) {
    return (
        <ul className="space-y-2">
            {movements.map((movement) => (
                <li key={movement.id} className="rounded-xl bg-slate-50 px-3 py-2 text-sm text-slate-700">
                    {movement.movement_type} • {movement.quantity} • {movement.occurred_at ? new Date(movement.occurred_at).toLocaleString() : '-'}
                    {movement.notes ? ` • ${movement.notes}` : ''}
                </li>
            ))}
            {movements.length === 0 ? <li className="text-sm text-slate-500">Sem movimentos.</li> : null}
        </ul>
    );
}
