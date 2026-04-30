import { router } from '@inertiajs/react';

type Props = {
    statuses: string[];
    indexRouteName: string;
};

export default function SpaceCleaningFilters({ statuses, indexRouteName }: Props) {
    return (
        <form
            className="grid gap-2 rounded-2xl border border-slate-200 bg-white p-4 md:grid-cols-4"
            onSubmit={(event) => {
                event.preventDefault();
                const form = new FormData(event.currentTarget);
                router.get(route(indexRouteName), {
                    status: form.get('status') as string,
                    space_id: form.get('space_id') as string,
                    space_reservation_id: form.get('space_reservation_id') as string,
                    assigned_to: form.get('assigned_to') as string,
                }, { preserveState: true, replace: true });
            }}
        >
            <select name="status" className="rounded-lg border-slate-300 text-sm">
                <option value="">Estado</option>
                {statuses.map((status) => <option key={status} value={status}>{status}</option>)}
            </select>
            <input name="space_id" placeholder="Espaco (id)" className="rounded-lg border-slate-300 text-sm" />
            <input name="space_reservation_id" placeholder="Reserva (id)" className="rounded-lg border-slate-300 text-sm" />
            <input name="assigned_to" placeholder="Responsavel (id)" className="rounded-lg border-slate-300 text-sm" />
            <button type="submit" className="rounded-lg bg-slate-900 px-3 py-2 text-xs font-medium text-white">Filtrar</button>
        </form>
    );
}
