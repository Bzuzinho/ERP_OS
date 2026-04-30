import { router } from '@inertiajs/react';
import { FormEvent, useState } from 'react';

type Props = {
    indexRouteName: string;
    initialFilters?: Record<string, string | boolean | undefined>;
};

export default function InventoryFilters({ indexRouteName, initialFilters = {} }: Props) {
    const [search, setSearch] = useState((initialFilters.search as string) ?? '');

    const submit = (event: FormEvent) => {
        event.preventDefault();
        router.get(route(indexRouteName), { ...initialFilters, search }, { preserveState: true, replace: true });
    };

    return (
        <form onSubmit={submit} className="rounded-2xl border border-slate-200 bg-white p-4">
            <div className="flex gap-2">
                <input value={search} onChange={(event) => setSearch(event.target.value)} placeholder="Pesquisar" className="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                <button type="submit" className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white">Filtrar</button>
            </div>
        </form>
    );
}
