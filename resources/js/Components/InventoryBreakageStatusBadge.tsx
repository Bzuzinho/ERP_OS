type Props = { status: string };

export default function InventoryBreakageStatusBadge({ status }: Props) {
    return <span className="inline-flex rounded-full bg-rose-100 px-2.5 py-1 text-xs font-semibold text-rose-800">{status}</span>;
}
