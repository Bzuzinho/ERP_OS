import AppBadge from '@/Components/App/AppBadge';
import AppCard from '@/Components/App/AppCard';
import EmptyState from '@/Components/App/EmptyState';
import FilterPills from '@/Components/App/FilterPills';
import FloatingActionButton from '@/Components/App/FloatingActionButton';
import SearchInput from '@/Components/App/SearchInput';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useMemo, useState } from 'react';

type Task = {
    id: number;
    title: string;
    status: string;
    priority: string;
    due_date?: string | null;
    assignee?: { id: number; name: string } | null;
};

type Props = {
    tasks: { data: Task[] };
    filters: { search?: string; status?: string; priority?: string; assignee?: string };
    statuses: string[];
    priorities: string[];
    users: { id: number; name: string }[];
};

export default function Index({ tasks, filters, statuses, priorities, users }: Props) {
    const [selectedStatus, setSelectedStatus] = useState(filters.status ?? '');
    const [search, setSearch] = useState(filters.search ?? '');

    const filteredTasks = useMemo(() => {
        if (!selectedStatus) return tasks.data;
        return tasks.data.filter((task) => task.status === selectedStatus);
    }, [tasks.data, selectedStatus]);

    const pendingCount = tasks.data.filter((task) => task.status.toLowerCase().includes('pend')).length;
    const inProgressCount = tasks.data.filter((task) => task.status.toLowerCase().includes('curso') || task.status.toLowerCase().includes('exec')).length;
    const doneCount = tasks.data.filter((task) => task.status.toLowerCase().includes('conc') || task.status.toLowerCase().includes('done')).length;

    const applyStatus = (status: string) => {
        setSelectedStatus(status);
        router.get(
            route('admin.tasks.index'),
            {
                ...filters,
                search: search || undefined,
                status: status || undefined,
            },
            { preserveState: true, replace: true },
        );
    };

    const statusTone = (status: string): 'blue' | 'amber' | 'green' | 'red' | 'slate' => {
        const normalized = status.toLowerCase();
        if (normalized.includes('conc') || normalized.includes('done')) return 'green';
        if (normalized.includes('curso') || normalized.includes('exec')) return 'blue';
        if (normalized.includes('pend')) return 'amber';
        return 'slate';
    };

    const priorityTone = (priority: string): 'blue' | 'amber' | 'green' | 'red' | 'slate' => {
        const normalized = priority.toLowerCase();
        if (normalized.includes('alta') || normalized.includes('urgent')) return 'red';
        if (normalized.includes('media') || normalized.includes('média')) return 'amber';
        if (normalized.includes('baixa')) return 'green';
        return 'slate';
    };

    return (
        <AdminLayout title="Tarefas" subtitle="Execução operacional do dia">
            <Head title="Tarefas" />
            <div className="space-y-6">
                <div className="lg:hidden">
                    <FilterPills
                        selected={selectedStatus}
                        onChange={applyStatus}
                        options={[
                            { label: 'Todas', value: '' },
                            { label: 'Pendentes', value: statuses.find((status) => status.toLowerCase().includes('pend')) ?? '' },
                            { label: 'Em curso', value: statuses.find((status) => status.toLowerCase().includes('curso') || status.toLowerCase().includes('exec')) ?? '' },
                            { label: 'Concluídas', value: statuses.find((status) => status.toLowerCase().includes('conc') || status.toLowerCase().includes('done')) ?? '' },
                        ]}
                    />
                </div>

                <AppCard>
                    <div className="grid grid-cols-3 gap-3 text-center">
                        <div>
                            <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Pendentes</p>
                            <p className="mt-1 text-2xl font-bold text-amber-600">{pendingCount}</p>
                        </div>
                        <div>
                            <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Em curso</p>
                            <p className="mt-1 text-2xl font-bold text-blue-600">{inProgressCount}</p>
                        </div>
                        <div>
                            <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">Concluídas</p>
                            <p className="mt-1 text-2xl font-bold text-emerald-600">{doneCount}</p>
                        </div>
                    </div>
                </AppCard>

                <AppCard className="hidden p-4 lg:block">
                    <div className="grid gap-3 md:grid-cols-4">
                        <SearchInput value={search} onChange={setSearch} placeholder="Pesquisar tarefa ou responsável" />
                        <select
                            value={selectedStatus}
                            onChange={(event) => applyStatus(event.target.value)}
                            className="rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700"
                        >
                            <option value="">Todos os estados</option>
                            {statuses.map((status) => (
                                <option key={status} value={status}>{status}</option>
                            ))}
                        </select>
                        <select
                            value={filters.priority ?? ''}
                            onChange={(event) =>
                                router.get(
                                    route('admin.tasks.index'),
                                    {
                                        ...filters,
                                        priority: event.target.value || undefined,
                                        search: search || undefined,
                                        status: selectedStatus || undefined,
                                    },
                                    { preserveState: true, replace: true },
                                )
                            }
                            className="rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700"
                        >
                            <option value="">Todas as prioridades</option>
                            {priorities.map((priority) => (
                                <option key={priority} value={priority}>{priority}</option>
                            ))}
                        </select>
                        <select
                            value={filters.assignee ?? ''}
                            onChange={(event) =>
                                router.get(
                                    route('admin.tasks.index'),
                                    {
                                        ...filters,
                                        assignee: event.target.value || undefined,
                                        search: search || undefined,
                                        status: selectedStatus || undefined,
                                    },
                                    { preserveState: true, replace: true },
                                )
                            }
                            className="rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700"
                        >
                            <option value="">Todos os responsáveis</option>
                            {users.map((user) => (
                                <option key={user.id} value={String(user.id)}>{user.name}</option>
                            ))}
                        </select>
                    </div>
                </AppCard>

                <AppCard className="hidden overflow-hidden p-0 lg:block">
                    <table className="min-w-full text-sm">
                        <thead className="bg-slate-50 text-left text-slate-500">
                            <tr>
                                <th className="px-4 py-3">Tarefa</th>
                                <th className="px-4 py-3">Responsável</th>
                                <th className="px-4 py-3">Estado</th>
                                <th className="px-4 py-3">Prioridade</th>
                                <th className="px-4 py-3">Prazo</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100 bg-white">
                            {filteredTasks.map((task) => (
                                <tr key={task.id}>
                                    <td className="px-4 py-3 font-semibold text-slate-900">
                                        <Link href={route('admin.tasks.show', task.id)}>{task.title}</Link>
                                    </td>
                                    <td className="px-4 py-3 text-slate-600">{task.assignee?.name ?? '-'}</td>
                                    <td className="px-4 py-3"><AppBadge tone={statusTone(task.status)}>{task.status}</AppBadge></td>
                                    <td className="px-4 py-3"><AppBadge tone={priorityTone(task.priority)}>{task.priority}</AppBadge></td>
                                    <td className="px-4 py-3 text-slate-600">{task.due_date ? new Date(task.due_date).toLocaleDateString() : '-'}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                    {filteredTasks.length === 0 ? <div className="p-6"><EmptyState title="Sem tarefas" description="Não há tarefas com os filtros atuais." /></div> : null}
                </AppCard>

                <div className="grid gap-3 lg:hidden">
                    {filteredTasks.map((task) => (
                        <AppCard key={task.id} className="p-4">
                            <Link href={route('admin.tasks.show', task.id)} className="flex items-start gap-3">
                                <span className="mt-1 inline-flex h-5 w-5 items-center justify-center rounded-md border border-slate-300 bg-white">
                                    <span className="h-2.5 w-2.5 rounded-sm bg-slate-200" />
                                </span>
                                <div className="min-w-0 flex-1">
                                    <p className="truncate text-sm font-semibold text-slate-900">{task.title}</p>
                                    <p className="mt-1 text-xs text-slate-500">{task.assignee?.name ?? 'Sem responsável'}</p>
                                    <div className="mt-3 flex flex-wrap items-center gap-2">
                                        <AppBadge tone={statusTone(task.status)}>{task.status}</AppBadge>
                                        <AppBadge tone={priorityTone(task.priority)}>{task.priority}</AppBadge>
                                        <span className="text-xs text-slate-500">{task.due_date ? new Date(task.due_date).toLocaleDateString() : 'Sem prazo'}</span>
                                    </div>
                                </div>
                            </Link>
                        </AppCard>
                    ))}
                    {filteredTasks.length === 0 ? <EmptyState title="Sem tarefas" description="Não há tarefas com os filtros atuais." /> : null}
                </div>
            </div>

            <div className="hidden lg:block">
                <Link href={route('admin.tasks.create')} className="fixed bottom-6 right-6 rounded-2xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-600/20 hover:bg-blue-700">
                    Nova tarefa
                </Link>
            </div>

            <FloatingActionButton href={route('admin.tasks.create')} label="Nova tarefa" />
        </AdminLayout>
    );
}
