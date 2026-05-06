import TaskChecklist from '@/Components/TaskChecklist';
import TaskPriorityBadge from '@/Components/TaskPriorityBadge';
import TaskStatusBadge from '@/Components/TaskStatusBadge';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router } from '@inertiajs/react';

type Props = {
    task: {
        id: number;
        title: string;
        description?: string | null;
        status: string;
        priority: string;
        ticket?: { id: number; reference: string; title: string } | null;
        assignee?: { id: number; name: string } | null;
        checklists: { id: number; title: string; items: { id: number; label: string; is_completed: boolean }[] }[];
    };
};

export default function Show({ task }: Props) {
    const complete = () => router.post(route('admin.tasks.complete', task.id));

    return (
        <AdminLayout title={task.title} subtitle="Detalhe da tarefa, progresso e checklist.">
            <Head title={task.title} />
            <div className="space-y-5">
                <div className="rounded-2xl border border-slate-200 bg-white p-6">
                    <div className="flex flex-wrap items-center justify-between gap-3">
                        <h1 className="text-2xl font-semibold text-slate-900">{task.title}</h1>
                        <div className="flex items-center gap-2">
                            <TaskStatusBadge status={task.status} />
                            <TaskPriorityBadge priority={task.priority} />
                        </div>
                    </div>
                    <p className="mt-3 text-sm text-slate-700">{task.description || 'Sem descricao.'}</p>
                    <p className="mt-3 text-sm text-slate-600">Responsavel: {task.assignee?.name ?? 'N/A'}</p>
                    <p className="text-sm text-slate-600">Ticket: {task.ticket ? `${task.ticket.reference} - ${task.ticket.title}` : 'N/A'}</p>
                    <div className="mt-4 flex items-center gap-2">
                        <button onClick={complete} className="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-500">Concluir</button>
                        <Link href={route('admin.tasks.edit', task.id)} className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Editar</Link>
                        <Link href={route('admin.tasks.index')} className="rounded-xl bg-slate-200 px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-300">← Lista de tarefas</Link>
                    </div>
                </div>

                <TaskChecklist checklists={task.checklists} taskId={task.id} taskStatus={task.status} isEditable={true} />
            </div>
        </AdminLayout>
    );
}
