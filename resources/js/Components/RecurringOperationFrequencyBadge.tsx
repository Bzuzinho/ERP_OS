type Props = { frequency: string; interval?: number };

export default function RecurringOperationFrequencyBadge({ frequency, interval = 1 }: Props) {
    return <span className="rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-800">{`${frequency} (${interval})`}</span>;
}
