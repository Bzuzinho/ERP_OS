import { router } from '@inertiajs/react';

type Props = {
    statuses: string[];
    indexRouteName: string;
    initialFilters?: { search?: string; status?: string; isPublic?: string; isActive?: string };
};

export default function SpaceFilters({ statuses, indexRouteName, initialFilters }: Props) {
    return (
        <form
            className="grid gap-2 rounded-2xl border border-slate-200 bg-white p-4 md:grid-cols-4"
            onSubmit={(event) => {
                event.preventDefault();
                const form = new FormData(event.currentTarget);
                router.get(route(indexRouteName), {
                    search: form.get('search') as string,
                    status: form.get('status') as string,
                    is_public: form.get('is_public') as string,
                    is_active: form.get('is_active') as string,
                }, { preserveState: true, replace: true });
            }}
        >
            <input name="search" defaultValue={initialFilters?.search ?? ''} placeholder="Pesquisar" className="rounded-lg border-slate-300 text-sm" />
            <select name="status" defaultValue={initialFilters?.status ?? ''} className="rounded-lg border-slate-300 text-sm">
                <option value="">Todos os estados</option>
                {statuses.map((status) => <option key={status} value={status}>{status}</option>)}
            </select>
            <select name="is_public" defaultValue={initialFilters?.isPublic ?? ''} className="rounded-lg border-slate-300 text-sm">
                <option value="">Publico?</option>
                <option value="true">Sim</option>
                <option value="false">Nao</option>
            </select>
            <select name="is_active" defaultValue={initialFilters?.isActive ?? ''} className="rounded-lg border-slate-300 text-sm">
                <option value="">Ativo?</option>
                <option value="true">Sim</option>
                <option value="false">Nao</option>
            </select>
            <button type="submit" className="rounded-lg bg-slate-900 px-3 py-2 text-xs font-medium text-white">Filtrar</button>
        </form>
    );
}
