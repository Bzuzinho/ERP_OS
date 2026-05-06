import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router } from '@inertiajs/react';
import { FormEvent, useState } from 'react';

type Option = { id: number; name?: string; reference?: string; title?: string };

type ChecklistItem = {
    id: string;
    label: string;
};

type Checklist = {
    id: string;
    title: string;
    items: ChecklistItem[];
};

type Props = {
    tickets: Option[];
    users: Option[];
    statuses: string[];
    priorities: string[];
};

export default function Create({ tickets, users, statuses, priorities }: Props) {
    const [formData, setFormData] = useState({
        title: '',
        description: '',
        status: statuses[0] ?? 'pending',
        priority: priorities[1] ?? priorities[0] ?? 'normal',
        assigned_to: '',
        ticket_id: '',
        due_date: '',
    });

    const [checklists, setChecklists] = useState<Checklist[]>([]);
    const [newChecklistTitle, setNewChecklistTitle] = useState('');
    const [newItemsByChecklist, setNewItemsByChecklist] = useState<Record<string, string>>({});
    const [isProcessing, setIsProcessing] = useState(false);

    const addChecklist = () => {
        if (!newChecklistTitle.trim()) return;

        const checklistId = `checklist_${Date.now()}`;
        setChecklists([
            ...checklists,
            {
                id: checklistId,
                title: newChecklistTitle,
                items: [],
            },
        ]);
        setNewChecklistTitle('');
    };

    const addChecklistItem = (checklistId: string) => {
        const itemLabel = newItemsByChecklist[checklistId]?.trim();
        if (!itemLabel) return;

        setChecklists(
            checklists.map((checklist) =>
                checklist.id === checklistId
                    ? {
                          ...checklist,
                          items: [...checklist.items, { id: `item_${Date.now()}_${Math.random()}`, label: itemLabel }],
                      }
                    : checklist,
            ),
        );
        setNewItemsByChecklist({ ...newItemsByChecklist, [checklistId]: '' });
    };

    const removeChecklist = (checklistId: string) => {
        setChecklists(checklists.filter((c) => c.id !== checklistId));
        const { [checklistId]: _, ...rest } = newItemsByChecklist;
        setNewItemsByChecklist(rest);
    };

    const removeChecklistItem = (checklistId: string, itemId: string) => {
        setChecklists(
            checklists.map((checklist) =>
                checklist.id === checklistId
                    ? { ...checklist, items: checklist.items.filter((item) => item.id !== itemId) }
                    : checklist,
            ),
        );
    };

    const submit = (event: FormEvent) => {
        event.preventDefault();
        setIsProcessing(true);

        router.post(route('admin.tasks.store'), {
            ...formData,
            checklists: JSON.stringify(checklists),
        });
    };

    return (
        <AdminLayout title="Nova tarefa" subtitle="Registe uma tarefa interna e associe-a a um ticket quando aplicavel.">
            <Head title="Nova tarefa" />
            <form onSubmit={submit} className="space-y-4 rounded-2xl border border-slate-200 bg-white p-6">
                <h1 className="text-xl font-semibold text-slate-900">Criar tarefa</h1>
                <input
                    value={formData.title}
                    onChange={(event) => setFormData({ ...formData, title: event.target.value })}
                    placeholder="Titulo"
                    className="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"
                />
                <textarea
                    value={formData.description}
                    onChange={(event) => setFormData({ ...formData, description: event.target.value })}
                    placeholder="Descricao"
                    className="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"
                    rows={4}
                />
                <div className="grid gap-3 md:grid-cols-3">
                    <select
                        value={formData.status}
                        onChange={(event) => setFormData({ ...formData, status: event.target.value })}
                        className="rounded-xl border border-slate-300 px-3 py-2 text-sm"
                    >
                        {statuses.map((status) => (
                            <option key={status} value={status}>
                                {status}
                            </option>
                        ))}
                    </select>
                    <select
                        value={formData.priority}
                        onChange={(event) => setFormData({ ...formData, priority: event.target.value })}
                        className="rounded-xl border border-slate-300 px-3 py-2 text-sm"
                    >
                        {priorities.map((priority) => (
                            <option key={priority} value={priority}>
                                {priority}
                            </option>
                        ))}
                    </select>
                    <input
                        type="date"
                        value={formData.due_date}
                        onChange={(event) => setFormData({ ...formData, due_date: event.target.value })}
                        className="rounded-xl border border-slate-300 px-3 py-2 text-sm"
                    />
                </div>
                <div className="grid gap-3 md:grid-cols-2">
                    <select
                        value={formData.assigned_to}
                        onChange={(event) => setFormData({ ...formData, assigned_to: event.target.value })}
                        className="rounded-xl border border-slate-300 px-3 py-2 text-sm"
                    >
                        <option value="">Responsavel</option>
                        {users.map((user) => (
                            <option key={user.id} value={user.id}>
                                {user.name}
                            </option>
                        ))}
                    </select>
                    <select
                        value={formData.ticket_id}
                        onChange={(event) => setFormData({ ...formData, ticket_id: event.target.value })}
                        className="rounded-xl border border-slate-300 px-3 py-2 text-sm"
                    >
                        <option value="">Ticket relacionado</option>
                        {tickets.map((ticket) => (
                            <option key={ticket.id} value={ticket.id}>
                                {ticket.reference} - {ticket.title}
                            </option>
                        ))}
                    </select>
                </div>

                {/* Checklists Section */}
                <div className="rounded-xl border border-slate-300 p-4">
                    <h3 className="text-sm font-semibold text-slate-900">Checklists</h3>

                    {checklists.map((checklist) => (
                        <div key={checklist.id} className="mt-3 rounded-lg border border-slate-200 bg-slate-50 p-3">
                            <div className="flex items-center justify-between">
                                <h4 className="text-sm font-medium text-slate-900">{checklist.title}</h4>
                                <button
                                    type="button"
                                    onClick={() => removeChecklist(checklist.id)}
                                    className="text-xs text-rose-600 hover:text-rose-700"
                                >
                                    Remover
                                </button>
                            </div>

                            <ul className="mt-2 space-y-2">
                                {checklist.items.map((item) => (
                                    <li key={item.id} className="flex items-center justify-between rounded bg-white p-2 text-sm">
                                        <span className="text-slate-700">{item.label}</span>
                                        <button
                                            type="button"
                                            onClick={() => removeChecklistItem(checklist.id, item.id)}
                                            className="text-xs text-rose-600 hover:text-rose-700"
                                        >
                                            ✕
                                        </button>
                                    </li>
                                ))}
                            </ul>

                            <div className="mt-2 flex gap-2">
                                <input
                                    type="text"
                                    placeholder="Novo item..."
                                    value={newItemsByChecklist[checklist.id] || ''}
                                    onChange={(e) => setNewItemsByChecklist({ ...newItemsByChecklist, [checklist.id]: e.target.value })}
                                    onKeyPress={(e) => {
                                        if (e.key === 'Enter') {
                                            e.preventDefault();
                                            addChecklistItem(checklist.id);
                                        }
                                    }}
                                    className="flex-1 rounded border border-slate-300 px-2 py-1 text-xs"
                                />
                                <button
                                    type="button"
                                    onClick={() => addChecklistItem(checklist.id)}
                                    className="rounded bg-slate-900 px-2 py-1 text-xs font-medium text-white hover:bg-slate-700"
                                >
                                    Adicionar
                                </button>
                            </div>
                        </div>
                    ))}

                    <div className="mt-3 flex gap-2">
                        <input
                            type="text"
                            placeholder="Nome da checklist..."
                            value={newChecklistTitle}
                            onChange={(e) => setNewChecklistTitle(e.target.value)}
                            onKeyPress={(e) => {
                                if (e.key === 'Enter') {
                                    e.preventDefault();
                                    addChecklist();
                                }
                            }}
                            className="flex-1 rounded border border-slate-300 px-2 py-1 text-sm"
                        />
                        <button
                            type="button"
                            onClick={addChecklist}
                            className="rounded bg-slate-900 px-3 py-1 text-sm font-medium text-white hover:bg-slate-700"
                        >
                            Adicionar Checklist
                        </button>
                    </div>
                </div>

                <button disabled={isProcessing} className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 disabled:opacity-60">
                    Guardar
                </button>
            </form>
        </AdminLayout>
    );
}
