type TimelineMode = 'admin' | 'portal';

type TimelineEntry = {
    id: number | string;
    kind: 'created' | 'status' | 'assignment' | 'comment' | 'attachment' | 'activity';
    title: string;
    description?: string | null;
    by?: string | null;
    at: string;
    visibility?: 'internal' | 'public' | null;
};

type TicketTimelineProps = {
    entries: TimelineEntry[];
    mode?: TimelineMode;
    title?: string;
    emptyText?: string;
};

export default function TicketTimeline({
    entries,
    mode = 'admin',
    title,
    emptyText = 'Sem eventos para apresentar.',
}: TicketTimelineProps) {
    const sortedEntries = [...entries].sort((a, b) => new Date(b.at).getTime() - new Date(a.at).getTime());

    const resolveBadge = (entry: TimelineEntry): string | null => {
        if (entry.kind === 'comment') {
            if (entry.visibility === 'internal') {
                return 'Interno';
            }

            return mode === 'portal' ? 'Mensagem' : 'Visivel ao municipe';
        }

        if (entry.kind === 'attachment') {
            return entry.visibility === 'internal' ? 'Anexo interno' : 'Anexo publico';
        }

        if (entry.kind === 'status') {
            return 'Estado';
        }

        if (entry.kind === 'assignment') {
            return 'Atribuicao';
        }

        return null;
    };

    return (
        <div className="rounded-2xl border border-slate-200 bg-white p-5">
            <h3 className="text-lg font-semibold text-slate-900">{title ?? (mode === 'admin' ? 'Historico completo' : 'Linha temporal')}</h3>
            <ul className="mt-4 space-y-3">
                {sortedEntries.length ? sortedEntries.map((entry) => (
                    <li key={entry.id} className="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <div className="flex flex-wrap items-center gap-2">
                            <p className="text-sm font-medium text-slate-900">{entry.title}</p>
                            {resolveBadge(entry) ? (
                                <span className="rounded-full bg-slate-200 px-2 py-0.5 text-[11px] font-semibold text-slate-700">
                                    {resolveBadge(entry)}
                                </span>
                            ) : null}
                        </div>
                        <p className="mt-1 text-xs text-slate-600">
                            {entry.by ?? 'Sistema'} - {new Date(entry.at).toLocaleString()}
                        </p>
                        {entry.description ? <p className="mt-2 text-sm text-slate-700">{entry.description}</p> : null}
                    </li>
                )) : (
                    <li className="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-3 text-sm text-slate-600">{emptyText}</li>
                )}
            </ul>
        </div>
    );
}
