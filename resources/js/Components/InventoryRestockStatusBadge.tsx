type Props = { status: string };

export default function InventoryRestockStatusBadge({ status }: Props) {
    return <span className="inline-flex rounded-full bg-violet-100 px-2.5 py-1 text-xs font-semibold text-violet-800">{status}</span>;
}
