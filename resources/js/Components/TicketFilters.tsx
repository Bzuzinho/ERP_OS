import { router } from '@inertiajs/react';
import { FormEvent, useMemo, useState } from 'react';

type TicketFiltersProps = {
    statuses: string[];
    priorities: string[];
    sources: string[];
    initialFilters: {
        search?: string;
        status?: string;
        priority?: string;
        source?: string;
    };
    indexRouteName: string;
};

export default function TicketFilters({
    statuses,
    priorities,
    sources,
    initialFilters,
    indexRouteName,
}: TicketFiltersProps) {
    const [search, setSearch] = useState(initialFilters.search ?? '');
    const [status, setStatus] = useState(initialFilters.status ?? '');
    const [priority, setPriority] = useState(initialFilters.priority ?? '');
    const [source, setSource] = useState(initialFilters.source ?? '');

    const query = useMemo(
        () => ({
            search: search || undefined,
            status: status || undefined,
            priority: priority || undefined,
            source: source || undefined,
        }),
        [search, status, priority, source],
    );

    const applyFilters = (event: FormEvent) => {
        event.preventDefault();

        router.get(route(indexRouteName), query, {
            preserveState: true,
            replace: true,
        });
    };

    return (
        <form onSubmit={applyFilters} className="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 md:grid-cols-5">
            <input
                type="text"
                value={search}
                onChange={(event) => setSearch(event.target.value)}
                placeholder="Pesquisar referencia, assunto, categoria"
                className="rounded-xl border border-slate-300 px-3 py-2 text-sm"
            />

            <select value={status} onChange={(event) => setStatus(event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                <option value="">Estado</option>
                {statuses.map((item) => (
                    <option key={item} value={item}>
                        {item}
                    </option>
                ))}
            </select>

            <select value={priority} onChange={(event) => setPriority(event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                <option value="">Prioridade</option>
                {priorities.map((item) => (
                    <option key={item} value={item}>
                        {item}
                    </option>
                ))}
            </select>

            <select value={source} onChange={(event) => setSource(event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                <option value="">Origem</option>
                {sources.map((item) => (
                    <option key={item} value={item}>
                        {item}
                    </option>
                ))}
            </select>

            <button type="submit" className="rounded-xl bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700">
                Filtrar
            </button>
        </form>
    );
}
