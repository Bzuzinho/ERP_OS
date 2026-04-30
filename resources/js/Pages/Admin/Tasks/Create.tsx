import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type Option = { id: number; name?: string; reference?: string; title?: string };

type Props = {
    tickets: Option[];
    users: Option[];
    statuses: string[];
    priorities: string[];
};

export default function Create({ tickets, users, statuses, priorities }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        title: '',
        description: '',
        status: statuses[0] ?? 'pending',
        priority: priorities[1] ?? priorities[0] ?? 'normal',
        assigned_to: '',
        ticket_id: '',
        due_date: '',
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        post(route('admin.tasks.store'));
    };

    return (
        <AdminLayout title="Nova tarefa" subtitle="Registe uma tarefa interna e associe-a a um ticket quando aplicavel.">
            <Head title="Nova tarefa" />
            <form onSubmit={submit} className="space-y-4 rounded-2xl border border-slate-200 bg-white p-6">
                <h1 className="text-xl font-semibold text-slate-900">Criar tarefa</h1>
                <input value={data.title} onChange={(event) => setData('title', event.target.value)} placeholder="Titulo" className="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                <textarea value={data.description} onChange={(event) => setData('description', event.target.value)} placeholder="Descricao" className="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" rows={4} />
                <div className="grid gap-3 md:grid-cols-3">
                    <select value={data.status} onChange={(event) => setData('status', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        {statuses.map((status) => <option key={status} value={status}>{status}</option>)}
                    </select>
                    <select value={data.priority} onChange={(event) => setData('priority', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        {priorities.map((priority) => <option key={priority} value={priority}>{priority}</option>)}
                    </select>
                    <input type="date" value={data.due_date} onChange={(event) => setData('due_date', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                </div>
                <div className="grid gap-3 md:grid-cols-2">
                    <select value={data.assigned_to} onChange={(event) => setData('assigned_to', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Responsavel</option>
                        {users.map((user) => <option key={user.id} value={user.id}>{user.name}</option>)}
                    </select>
                    <select value={data.ticket_id} onChange={(event) => setData('ticket_id', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Ticket relacionado</option>
                        {tickets.map((ticket) => <option key={ticket.id} value={ticket.id}>{ticket.reference} - {ticket.title}</option>)}
                    </select>
                </div>
                {Object.keys(errors).length > 0 ? <p className="text-sm text-rose-700">Verifique os campos obrigatorios.</p> : null}
                <button disabled={processing} className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 disabled:opacity-60">Guardar</button>
            </form>
        </AdminLayout>
    );
}
