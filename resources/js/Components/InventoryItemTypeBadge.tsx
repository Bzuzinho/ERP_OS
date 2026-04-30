type Props = { itemType: string };

export default function InventoryItemTypeBadge({ itemType }: Props) {
    return <span className="inline-flex rounded-full bg-sky-100 px-2.5 py-1 text-xs font-semibold text-sky-800">{itemType}</span>;
}
