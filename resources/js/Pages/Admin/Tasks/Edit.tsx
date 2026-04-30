import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type Option = { id: number; name?: string; reference?: string; title?: string };

type Props = {
    task: { id: number; title: string; description?: string; priority: string; assigned_to?: number | null; ticket_id?: number | null; due_date?: string | null };
    tickets: Option[];
    users: Option[];
    priorities: string[];
};

export default function Edit({ task, tickets, users, priorities }: Props) {
    const { data, setData, patch, processing } = useForm({
        title: task.title,
        description: task.description ?? '',
        priority: task.priority,
        assigned_to: task.assigned_to ? String(task.assigned_to) : '',
        ticket_id: task.ticket_id ? String(task.ticket_id) : '',
        due_date: task.due_date ?? '',
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        patch(route('admin.tasks.update', task.id));
    };

    return (
        <AdminLayout title="Editar tarefa" subtitle="Atualize dados, prioridade e ligacao da tarefa.">
            <Head title="Editar tarefa" />
            <form onSubmit={submit} className="space-y-4 rounded-2xl border border-slate-200 bg-white p-6">
                <h1 className="text-xl font-semibold text-slate-900">Editar tarefa</h1>
                <input value={data.title} onChange={(event) => setData('title', event.target.value)} className="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                <textarea value={data.description} onChange={(event) => setData('description', event.target.value)} className="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" rows={4} />
                <div className="grid gap-3 md:grid-cols-3">
                    <select value={data.priority} onChange={(event) => setData('priority', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        {priorities.map((priority) => <option key={priority} value={priority}>{priority}</option>)}
                    </select>
                    <select value={data.assigned_to} onChange={(event) => setData('assigned_to', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Responsavel</option>
                        {users.map((user) => <option key={user.id} value={user.id}>{user.name}</option>)}
                    </select>
                    <input type="date" value={data.due_date} onChange={(event) => setData('due_date', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                </div>
                <select value={data.ticket_id} onChange={(event) => setData('ticket_id', event.target.value)} className="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Ticket relacionado</option>
                    {tickets.map((ticket) => <option key={ticket.id} value={ticket.id}>{ticket.reference} - {ticket.title}</option>)}
                </select>
                <button disabled={processing} className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 disabled:opacity-60">Atualizar</button>
            </form>
        </AdminLayout>
    );
}
