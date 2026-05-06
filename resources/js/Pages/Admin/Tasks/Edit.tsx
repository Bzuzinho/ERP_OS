import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router, useForm } from '@inertiajs/react';
import { FormEvent, useState } from 'react';

type Option = { id: number; name?: string; reference?: string; title?: string };

type ChecklistItem = { id: number; label: string; is_completed: boolean };
type Checklist = { id: number; title: string; items: ChecklistItem[] };

type Props = {
    task: {
        id: number;
        title: string;
        description?: string;
        status: string;
        priority: string;
        assigned_to?: number | null;
        ticket_id?: number | null;
        due_date?: string | null;
        checklists: Checklist[];
    };
    tickets: Option[];
    users: Option[];
    statuses: string[];
    priorities: string[];
    canForceDelete: boolean;
};

export default function Edit({ task, tickets, users, statuses, priorities, canForceDelete }: Props) {
    const { data, setData, patch, processing } = useForm({
        title: task.title,
        description: task.description ?? '',
        status: task.status,
        priority: task.priority,
        assigned_to: task.assigned_to ? String(task.assigned_to) : '',
        ticket_id: task.ticket_id ? String(task.ticket_id) : '',
        due_date: task.due_date ?? '',
    });

    const [newChecklistTitle, setNewChecklistTitle] = useState('');
    const [newItemLabels, setNewItemLabels] = useState<Record<number, string>>({});
    const [editingChecklistTitles, setEditingChecklistTitles] = useState<Record<number, string>>(
        Object.fromEntries(task.checklists.map((checklist) => [checklist.id, checklist.title])),
    );
    const [editingItemLabels, setEditingItemLabels] = useState<Record<number, string>>(
        Object.fromEntries(
            task.checklists
                .flatMap((checklist) => checklist.items)
                .map((item) => [item.id, item.label]),
        ),
    );

    const submit = (event: FormEvent) => {
        event.preventDefault();
        patch(route('admin.tasks.update', task.id));
    };

    const handleCancel = () => {
        window.history.back();
    };

    const createChecklist = () => {
        if (!newChecklistTitle.trim()) return;

        router.post(
            route('admin.tasks.checklists.store', task.id),
            { title: newChecklistTitle.trim() },
            {
                preserveScroll: true,
                onSuccess: () => setNewChecklistTitle(''),
            },
        );
    };

    const updateChecklist = (checklistId: number) => {
        const title = editingChecklistTitles[checklistId]?.trim();
        if (!title) return;

        router.patch(
            route('admin.tasks.checklists.update', { task: task.id, checklist: checklistId }),
            { title },
            { preserveScroll: true },
        );
    };

    const deleteChecklist = (checklistId: number) => {
        if (!window.confirm('Remover esta checklist e todos os itens?')) return;

        router.delete(route('admin.tasks.checklists.destroy', { task: task.id, checklist: checklistId }), {
            preserveScroll: true,
        });
    };

    const createChecklistItem = (checklistId: number) => {
        const label = newItemLabels[checklistId]?.trim();
        if (!label) return;

        router.post(
            route('admin.tasks.checklists.items.store', { task: task.id, checklist: checklistId }),
            { label },
            {
                preserveScroll: true,
                onSuccess: () => setNewItemLabels((prev) => ({ ...prev, [checklistId]: '' })),
            },
        );
    };

    const updateChecklistItem = (checklistId: number, itemId: number, isCompleted?: boolean) => {
        const payload: { label?: string; is_completed?: boolean } = {};

        if (typeof isCompleted === 'boolean') {
            payload.is_completed = isCompleted;
        } else {
            const label = editingItemLabels[itemId]?.trim();
            if (!label) return;
            payload.label = label;
        }

        router.patch(
            route('admin.tasks.checklists.items.update', { task: task.id, checklist: checklistId, item: itemId }),
            payload,
            { preserveScroll: true },
        );
    };

    const deleteChecklistItem = (checklistId: number, itemId: number) => {
        router.delete(
            route('admin.tasks.checklists.items.destroy', { task: task.id, checklist: checklistId, item: itemId }),
            { preserveScroll: true },
        );
    };

    const forceDeleteTask = () => {
        if (!window.confirm('Apagar definitivamente esta tarefa? Esta acao nao pode ser desfeita.')) return;

        router.delete(route('admin.tasks.destroy', task.id), {
            data: { force: true },
        });
    };

    return (
        <AdminLayout title="Editar tarefa" subtitle="Atualize dados, prioridade e ligacao da tarefa.">
            <Head title="Editar tarefa" />
            <form onSubmit={submit} className="space-y-4 rounded-2xl border border-slate-200 bg-white p-6">
                <h1 className="text-xl font-semibold text-slate-900">Editar tarefa</h1>
                <input value={data.title} onChange={(event) => setData('title', event.target.value)} className="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                <textarea value={data.description} onChange={(event) => setData('description', event.target.value)} className="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" rows={4} />
                <div className="grid gap-3 md:grid-cols-2 lg:grid-cols-4">
                    <select value={data.status} onChange={(event) => setData('status', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        {statuses.map((status) => <option key={status} value={status}>{status}</option>)}
                    </select>
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

                <section className="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <h2 className="text-sm font-semibold text-slate-900">Checklist da tarefa</h2>

                    <div className="mt-3 flex gap-2">
                        <input
                            value={newChecklistTitle}
                            onChange={(event) => setNewChecklistTitle(event.target.value)}
                            placeholder="Nova checklist"
                            className="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"
                        />
                        <button
                            type="button"
                            onClick={createChecklist}
                            className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700"
                        >
                            Adicionar checklist
                        </button>
                    </div>

                    <div className="mt-4 space-y-3">
                        {task.checklists.map((checklist) => (
                            <div key={checklist.id} className="rounded-xl border border-slate-200 bg-white p-3">
                                <div className="flex flex-wrap items-center gap-2">
                                    <input
                                        value={editingChecklistTitles[checklist.id] ?? checklist.title}
                                        onChange={(event) => setEditingChecklistTitles((prev) => ({ ...prev, [checklist.id]: event.target.value }))}
                                        className="min-w-0 flex-1 rounded-xl border border-slate-300 px-3 py-2 text-sm"
                                    />
                                    <button
                                        type="button"
                                        onClick={() => updateChecklist(checklist.id)}
                                        className="rounded-xl bg-slate-900 px-3 py-2 text-xs font-medium text-white hover:bg-slate-700"
                                    >
                                        Guardar titulo
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => deleteChecklist(checklist.id)}
                                        className="rounded-xl bg-rose-600 px-3 py-2 text-xs font-medium text-white hover:bg-rose-500"
                                    >
                                        Remover checklist
                                    </button>
                                </div>

                                <div className="mt-3 space-y-2">
                                    {checklist.items.map((item) => (
                                        <div key={item.id} className="flex flex-wrap items-center gap-2 rounded-lg border border-slate-200 p-2">
                                            <input
                                                type="checkbox"
                                                checked={item.is_completed}
                                                onChange={(event) => updateChecklistItem(checklist.id, item.id, event.target.checked)}
                                            />
                                            <input
                                                value={editingItemLabels[item.id] ?? item.label}
                                                onChange={(event) => setEditingItemLabels((prev) => ({ ...prev, [item.id]: event.target.value }))}
                                                className="min-w-0 flex-1 rounded-xl border border-slate-300 px-3 py-2 text-sm"
                                            />
                                            <button
                                                type="button"
                                                onClick={() => updateChecklistItem(checklist.id, item.id)}
                                                className="rounded-xl bg-slate-900 px-3 py-2 text-xs font-medium text-white hover:bg-slate-700"
                                            >
                                                Guardar item
                                            </button>
                                            <button
                                                type="button"
                                                onClick={() => deleteChecklistItem(checklist.id, item.id)}
                                                className="rounded-xl bg-rose-600 px-3 py-2 text-xs font-medium text-white hover:bg-rose-500"
                                            >
                                                Remover item
                                            </button>
                                        </div>
                                    ))}
                                </div>

                                <div className="mt-3 flex gap-2">
                                    <input
                                        value={newItemLabels[checklist.id] ?? ''}
                                        onChange={(event) => setNewItemLabels((prev) => ({ ...prev, [checklist.id]: event.target.value }))}
                                        placeholder="Novo item da checklist"
                                        className="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"
                                    />
                                    <button
                                        type="button"
                                        onClick={() => createChecklistItem(checklist.id)}
                                        className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700"
                                    >
                                        Adicionar item
                                    </button>
                                </div>
                            </div>
                        ))}
                    </div>
                </section>

                <div className="flex gap-2">
                    <button disabled={processing} className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 disabled:opacity-60">
                        Atualizar
                    </button>
                    <button type="button" onClick={handleCancel} className="rounded-xl bg-slate-300 px-4 py-2 text-sm font-medium text-slate-900 hover:bg-slate-200">
                        Cancelar
                    </button>
                    {canForceDelete ? (
                        <button
                            type="button"
                            onClick={forceDeleteTask}
                            className="rounded-xl bg-rose-700 px-4 py-2 text-sm font-medium text-white hover:bg-rose-600"
                        >
                            Apagar definitivamente
                        </button>
                    ) : null}
                </div>
            </form>
        </AdminLayout>
    );
}
