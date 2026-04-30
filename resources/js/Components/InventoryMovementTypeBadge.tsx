type Props = { movementType: string };

export default function InventoryMovementTypeBadge({ movementType }: Props) {
    return <span className="inline-flex rounded-full bg-indigo-100 px-2.5 py-1 text-xs font-semibold text-indigo-800">{movementType}</span>;
}
