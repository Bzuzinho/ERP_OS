import TaskPriorityBadge from '@/Components/TaskPriorityBadge';
import TaskStatusBadge from '@/Components/TaskStatusBadge';
import { Link } from '@inertiajs/react';

type TaskCardProps = {
    task: {
        id: number;
        title: string;
        status: string;
        priority: string;
        due_date?: string | null;
        assignee?: { id: number; name: string } | null;
    };
};

export default function TaskCard({ task }: TaskCardProps) {
    return (
        <article className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div className="flex items-center justify-between gap-2">
                <Link href={route('admin.tasks.show', task.id)} className="font-semibold text-slate-900 hover:text-slate-700">
                    {task.title}
                </Link>
                <TaskStatusBadge status={task.status} />
            </div>
            <div className="mt-3 flex items-center gap-2">
                <TaskPriorityBadge priority={task.priority} />
                <span className="text-xs text-slate-600">{task.assignee?.name ?? 'Sem responsavel'}</span>
                <span className="text-xs text-slate-500">{task.due_date ? new Date(task.due_date).toLocaleDateString() : 'Sem prazo'}</span>
            </div>
        </article>
    );
}
