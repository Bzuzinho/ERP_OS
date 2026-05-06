import { router } from '@inertiajs/react';
import { useState } from 'react';

type ChecklistItem = {
    id: number;
    label: string;
    is_completed: boolean;
};

type Checklist = {
    id: number;
    title: string;
    items: ChecklistItem[];
};

type TaskChecklistProps = {
    checklists: Checklist[];
    taskId?: number;
    taskStatus?: string;
    isEditable?: boolean;
};

export default function TaskChecklist({ checklists, taskId, taskStatus, isEditable = true }: TaskChecklistProps) {
    const [loadingItems, setLoadingItems] = useState<Set<number>>(new Set());

    const allItems = checklists.flatMap((c) => c.items);
    const totalItems = allItems.length;
    const completedCount = allItems.filter((i) => i.is_completed).length;

    const toggleChecklistItem = (checklistId: number, itemId: number, currentState: boolean) => {
        if (!taskId || !isEditable) return;

        const willBeCompleted = !currentState;
        const newCompletedCount = willBeCompleted ? completedCount + 1 : completedCount - 1;
        const allWillBeDone = totalItems > 0 && newCompletedCount === totalItems;
        const isFirstCompletion = willBeCompleted && completedCount === 0 && taskStatus === 'pending';
        const shouldRevertToInProgress = !willBeCompleted && (taskStatus === 'done' || taskStatus === 'completed');

        setLoadingItems((prev) => new Set(prev).add(itemId));

        router.patch(
            route('admin.tasks.checklists.items.update', { task: taskId, checklist: checklistId, item: itemId }),
            { is_completed: willBeCompleted },
            {
                preserveScroll: true,
                onSuccess: () => {
                    if (allWillBeDone) {
                        if (window.confirm('Todos os itens da checklist foram concluidos. Marcar a tarefa como concluida?')) {
                            router.post(route('admin.tasks.complete', taskId));
                        }
                    } else if (isFirstCompletion) {
                        router.patch(route('admin.tasks.status.update', taskId), { status: 'in_progress' }, { preserveScroll: true });
                    } else if (shouldRevertToInProgress) {
                        router.patch(route('admin.tasks.status.update', taskId), { status: 'in_progress' }, { preserveScroll: true });
                    }
                },
                onFinish: () => setLoadingItems((prev) => {
                    const s = new Set(prev);
                    s.delete(itemId);
                    return s;
                }),
            },
        );
    };

    return (
        <div className="rounded-2xl border border-slate-200 bg-white p-5">
            <h3 className="text-lg font-semibold text-slate-900">Checklist</h3>
            {totalItems > 0 ? (
                <p className="mt-1 text-xs text-slate-500">{completedCount} / {totalItems} itens concluidos</p>
            ) : null}
            <div className="mt-3 space-y-3">
                {checklists.map((checklist) => (
                    <section key={checklist.id} className="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <h4 className="text-sm font-semibold text-slate-900">{checklist.title}</h4>
                        <ul className="mt-2 space-y-1 text-sm text-slate-700">
                            {checklist.items.map((item) => (
                                <li
                                    key={item.id}
                                    onClick={() => toggleChecklistItem(checklist.id, item.id, item.is_completed)}
                                    className={`flex items-center gap-2 cursor-pointer py-1 px-2 rounded transition-colors ${
                                        isEditable ? 'hover:bg-slate-200' : ''
                                    } ${item.is_completed ? 'text-slate-500' : ''} ${loadingItems.has(item.id) ? 'opacity-60' : ''}`}
                                >
                                    <input
                                        type="checkbox"
                                        checked={item.is_completed}
                                        onChange={() => toggleChecklistItem(checklist.id, item.id, item.is_completed)}
                                        disabled={!isEditable}
                                        className="cursor-pointer"
                                    />
                                    <span className={item.is_completed ? 'line-through' : ''}>{item.label}</span>
                                </li>
                            ))}
                        </ul>
                    </section>
                ))}
                {checklists.length === 0 ? <p className="text-sm text-slate-500">Sem checklists.</p> : null}
            </div>
        </div>
    );
}
