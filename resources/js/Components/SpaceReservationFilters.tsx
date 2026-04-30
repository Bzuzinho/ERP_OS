import { router } from '@inertiajs/react';

type Option = { id: number; name: string };

type Props = {
    statuses: string[];
    spaces: Option[];
    indexRouteName: string;
};

export default function SpaceReservationFilters({ statuses, spaces, indexRouteName }: Props) {
    return (
        <form
            className="grid gap-2 rounded-2xl border border-slate-200 bg-white p-4 md:grid-cols-4"
            onSubmit={(event) => {
                event.preventDefault();
                const form = new FormData(event.currentTarget);
                router.get(route(indexRouteName), {
                    search: form.get('search') as string,
                    status: form.get('status') as string,
                    space_id: form.get('space_id') as string,
                    date: form.get('date') as string,
                }, { preserveState: true, replace: true });
            }}
        >
            <input name="search" placeholder="Pesquisar" className="rounded-lg border-slate-300 text-sm" />
            <select name="status" className="rounded-lg border-slate-300 text-sm">
                <option value="">Todos os estados</option>
                {statuses.map((status) => <option key={status} value={status}>{status}</option>)}
            </select>
            <select name="space_id" className="rounded-lg border-slate-300 text-sm">
                <option value="">Todos os espacos</option>
                {spaces.map((space) => <option key={space.id} value={space.id}>{space.name}</option>)}
            </select>
            <input name="date" type="date" className="rounded-lg border-slate-300 text-sm" />
            <button type="submit" className="rounded-lg bg-slate-900 px-3 py-2 text-xs font-medium text-white">Filtrar</button>
        </form>
    );
}
