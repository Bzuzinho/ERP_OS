type EventTypeBadgeProps = { eventType: string };

export default function EventTypeBadge({ eventType }: EventTypeBadgeProps) {
    return <span className="inline-flex rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700">{eventType}</span>;
}
