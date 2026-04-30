type TimelineEntry = {
    id: number;
    old_status: string | null;
    new_status: string;
    notes: string | null;
    created_at: string;
    changed_by?: {
        id: number;
        name: string;
    } | null;
};

type TicketTimelineProps = {
    entries: TimelineEntry[];
};

export default function TicketTimeline({ entries }: TicketTimelineProps) {
    return (
        <div className="rounded-2xl border border-slate-200 bg-white p-5">
            <h3 className="text-lg font-semibold text-slate-900">Timeline de estados</h3>
            <ul className="mt-4 space-y-3">
                {entries.map((entry) => (
                    <li key={entry.id} className="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <p className="text-sm font-medium text-slate-900">
                            {entry.old_status ? `${entry.old_status} -> ${entry.new_status}` : entry.new_status}
                        </p>
                        <p className="mt-1 text-xs text-slate-600">
                            {entry.changed_by?.name ?? 'Sistema'} - {new Date(entry.created_at).toLocaleString()}
                        </p>
                        {entry.notes ? <p className="mt-2 text-sm text-slate-700">{entry.notes}</p> : null}
                    </li>
                ))}
            </ul>
        </div>
    );
}
