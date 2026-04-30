import TaskCard from '@/Components/TaskCard';
import TaskFilters from '@/Components/TaskFilters';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link } from '@inertiajs/react';

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
    return (
        <AdminLayout title="Tarefas" subtitle="Gestao de tarefas internas com filtros por estado, prioridade e responsavel.">
            <Head title="Tarefas" />
            <div className="space-y-6">
                <div className="flex items-center justify-between gap-4">
                    <h1 className="text-2xl font-semibold text-slate-900">Tarefas</h1>
                    <Link href={route('admin.tasks.create')} className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                        Nova tarefa
                    </Link>
                </div>

                <TaskFilters statuses={statuses} priorities={priorities} users={users} initialFilters={filters} />

                <div className="grid gap-3">
                    {tasks.data.map((task) => <TaskCard key={task.id} task={task} />)}
                    {tasks.data.length === 0 ? <p className="text-sm text-slate-500">Sem tarefas encontradas.</p> : null}
                </div>
            </div>
        </AdminLayout>
    );
}
