import { router } from '@inertiajs/react';
import { FormEvent, useState } from 'react';

type Option = { id: number; name: string };

type TaskFiltersProps = {
    statuses: string[];
    priorities: string[];
    users: Option[];
    initialFilters: { search?: string; status?: string; priority?: string; assignee?: string };
};

export default function TaskFilters({ statuses, priorities, users, initialFilters }: TaskFiltersProps) {
    const [search, setSearch] = useState(initialFilters.search ?? '');
    const [status, setStatus] = useState(initialFilters.status ?? '');
    const [priority, setPriority] = useState(initialFilters.priority ?? '');
    const [assignee, setAssignee] = useState(initialFilters.assignee ?? '');

    const submit = (event: FormEvent) => {
        event.preventDefault();

        router.get(route('admin.tasks.index'), {
            search: search || undefined,
            status: status || undefined,
            priority: priority || undefined,
            assignee: assignee || undefined,
        }, { preserveState: true, replace: true });
    };

    return (
        <form onSubmit={submit} className="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 md:grid-cols-5">
            <input value={search} onChange={(event) => setSearch(event.target.value)} placeholder="Pesquisar tarefa" className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
            <select value={status} onChange={(event) => setStatus(event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                <option value="">Estado</option>
                {statuses.map((item) => <option key={item} value={item}>{item}</option>)}
            </select>
            <select value={priority} onChange={(event) => setPriority(event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                <option value="">Prioridade</option>
                {priorities.map((item) => <option key={item} value={item}>{item}</option>)}
            </select>
            <select value={assignee} onChange={(event) => setAssignee(event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                <option value="">Responsavel</option>
                {users.map((item) => <option key={item.id} value={item.id}>{item.name}</option>)}
            </select>
            <button type="submit" className="rounded-xl bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700">Filtrar</button>
        </form>
    );
}
