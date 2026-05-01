type Task = { id: number; title: string; status: string };

type Props = { tasks: Task[] };

export default function OperationalPlanTaskList({ tasks }: Props) {
    return (
        <ul className="space-y-2 text-sm">
            {tasks.map((task) => (
                <li key={task.id} className="rounded-xl bg-slate-50 px-3 py-2 text-slate-700">
                    {task.title} - {task.status}
                </li>
            ))}
            {tasks.length === 0 ? <li className="text-slate-500">Sem tarefas associadas.</li> : null}
        </ul>
    );
}
