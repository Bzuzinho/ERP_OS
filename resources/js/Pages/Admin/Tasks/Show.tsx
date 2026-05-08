import TaskChecklist from '@/Components/TaskChecklist';
import TaskPriorityBadge from '@/Components/TaskPriorityBadge';
import TaskStatusBadge from '@/Components/TaskStatusBadge';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';

type Props = {
    task: {
        id: number;
        title: string;
        description?: string | null;
        status: string;
        priority: string;
        validated_at?: string | null;
        validation_notes?: string | null;
        observations?: string | null;
        reopen_count?: number;
        ticket?: { id: number; reference: string; title: string } | null;
        assignee?: { id: number; name: string } | null;
        validator?: { id: number; name: string } | null;
        checklists: { id: number; title: string; items: { id: number; label: string; is_completed: boolean }[] }[];
    };
    can: {
        submitValidation: boolean;
        validate: boolean;
        reopen: boolean;
    };
};

export default function Show({ task, can }: Props) {
    const submitValidationForm = useForm({});
    const validateForm = useForm({ validation_notes: '' });
    const reopenForm = useForm({ reason: '', target_status: 'reopened' as 'reopened' | 'in_progress' });

    const complete = () => submitValidationForm.post(route('admin.tasks.complete', task.id));
    const submitForValidation = () => submitValidationForm.post(route('admin.tasks.submit-validation', task.id));
    const validate = () => validateForm.post(route('admin.tasks.validate', task.id));
    const reopen = () => reopenForm.post(route('admin.tasks.reopen', task.id));

    const canSubmitForValidation = task.status === 'done' || task.status === 'reopened';
    const canValidateTask = task.status === 'pending_validation';
    const canReopenTask = ['pending_validation', 'validated', 'done'].includes(task.status);

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

                    <div className="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
                        <p><span className="font-semibold text-slate-900">Validacao:</span> {task.status === 'validated' ? 'Concluida' : 'Pendente'}</p>
                        {task.validator ? <p className="mt-1"><span className="font-semibold text-slate-900">Validado por:</span> {task.validator.name}</p> : null}
                        {task.validated_at ? <p className="mt-1"><span className="font-semibold text-slate-900">Data validacao:</span> {new Date(task.validated_at).toLocaleString()}</p> : null}
                        {task.validation_notes ? <p className="mt-1"><span className="font-semibold text-slate-900">Notas validacao:</span> {task.validation_notes}</p> : null}
                        {task.observations ? <p className="mt-1"><span className="font-semibold text-slate-900">Observacoes:</span> {task.observations}</p> : null}
                        {(task.reopen_count ?? 0) > 0 ? <p className="mt-1"><span className="font-semibold text-slate-900">Reaberturas:</span> {task.reopen_count}</p> : null}
                    </div>

                    <div className="mt-4 flex items-center gap-2">
                        <button onClick={complete} className="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-500">Concluir</button>
                        {can.submitValidation && canSubmitForValidation ? (
                            <button onClick={submitForValidation} className="rounded-xl bg-orange-600 px-4 py-2 text-sm font-medium text-white hover:bg-orange-500">Enviar para validacao</button>
                        ) : null}
                        {can.validate && canValidateTask ? (
                            <button onClick={validate} className="rounded-xl bg-blue-700 px-4 py-2 text-sm font-medium text-white hover:bg-blue-600">Validar tarefa</button>
                        ) : null}
                        {can.reopen && canReopenTask ? (
                            <button onClick={reopen} className="rounded-xl bg-indigo-700 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-600">Reabrir tarefa</button>
                        ) : null}
                        <Link href={route('admin.tasks.edit', task.id)} className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Editar</Link>
                        <Link href={route('admin.tasks.index')} className="rounded-xl bg-slate-200 px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-300">← Lista de tarefas</Link>
                    </div>

                    {can.validate && canValidateTask ? (
                        <div className="mt-3 rounded-xl border border-blue-200 bg-blue-50 p-3">
                            <label className="mb-1 block text-xs font-semibold text-blue-900">Notas de validacao</label>
                            <textarea
                                value={validateForm.data.validation_notes}
                                onChange={(event) => validateForm.setData('validation_notes', event.target.value)}
                                className="w-full rounded-xl border border-blue-300 px-3 py-2 text-sm"
                                placeholder="Adicionar observacoes de validacao"
                            />
                        </div>
                    ) : null}

                    {can.reopen && canReopenTask ? (
                        <div className="mt-3 rounded-xl border border-indigo-200 bg-indigo-50 p-3">
                            <label className="mb-1 block text-xs font-semibold text-indigo-900">Motivo da reabertura</label>
                            <textarea
                                value={reopenForm.data.reason}
                                onChange={(event) => reopenForm.setData('reason', event.target.value)}
                                className="w-full rounded-xl border border-indigo-300 px-3 py-2 text-sm"
                                placeholder="Explicar motivo da reabertura"
                            />
                        </div>
                    ) : null}
                </div>

                <TaskChecklist checklists={task.checklists} taskId={task.id} taskStatus={task.status} isEditable={true} />
            </div>
        </AdminLayout>
    );
}
