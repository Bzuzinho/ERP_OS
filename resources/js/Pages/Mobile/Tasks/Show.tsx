import MobileLayout from '@/Layouts/MobileLayout';
import { Head, router } from '@inertiajs/react';
import { type PageProps } from '@/types';
import { useState } from 'react';

type TaskChecklist = {
    id: number;
    title: string;
    is_completed: boolean;
    items?: Array<{ id: number; description: string; is_completed: boolean }>;
};

type Attachment = {
    id: number;
    file_name: string;
    size: number;
    uploaded_by?: { id: number; name: string };
};

type Comment = {
    id: number;
    body: string;
    user?: { id: number; name: string };
    created_at: string;
};

type Task = {
    id: number;
    title: string;
    description: string | null;
    status: string;
    priority: string;
    due_date: string | null;
    start_date: string | null;
    observations: string | null;
    validation_notes: string | null;
    reopen_count: number;
    ticket?: { id: number; reference: string; title: string };
    assignee?: { id: number; name: string };
    creator?: { id: number; name: string };
    validator?: { id: number; name: string };
    checklists: TaskChecklist[];
    comments: Comment[];
    attachments: Attachment[];
};

type ShowProps = {
    task: Task;
    can: {
        start: boolean;
        complete: boolean;
        submitValidation: boolean;
        validate: boolean;
        reopen: boolean;
    };
};

export default function TaskShow(props: PageProps<ShowProps>) {
    const { task, can } = props;
    const [processing, setProcessing] = useState(false);
    const [showObservationForm, setShowObservationForm] = useState(false);
    const [observationText, setObservationText] = useState('');
    const [showFileUpload, setShowFileUpload] = useState(false);

    const withLoading = (callback: () => void) => {
        setProcessing(true);
        callback();
    };

    const handleStart = () => {
        withLoading(() => {
            router.post(route('mobile.tasks.start', task.id), {}, {
                onFinish: () => setProcessing(false),
            });
        });
    };

    const handleComplete = () => {
        withLoading(() => {
            router.post(route('mobile.tasks.complete', task.id), {}, {
                onFinish: () => setProcessing(false),
            });
        });
    };

    const handleSubmitValidation = () => {
        withLoading(() => {
            router.post(route('mobile.tasks.submit-validation', task.id), {}, {
                onFinish: () => setProcessing(false),
            });
        });
    };

    const handleAddObservation = (e: React.FormEvent) => {
        e.preventDefault();
        withLoading(() => {
            router.post(
                route('mobile.tasks.observations.store', task.id),
                { observation: observationText },
                {
                    onSuccess: () => {
                        setObservationText('');
                        setShowObservationForm(false);
                    },
                    onFinish: () => setProcessing(false),
                }
            );
        });
    };

    const handleChecklistToggle = (checklistId: number, isCompleted: boolean) => {
        router.post(
            route('mobile.tasks.checklists.update', { task: task.id, checklist: checklistId }),
            { is_completed: isCompleted }
        );
    };

    const handleFileUpload = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        const data = new FormData(e.currentTarget);
        router.post(
            route('mobile.tasks.attachments.store', task.id),
            data,
            {
                onSuccess: () => setShowFileUpload(false),
                forceFormData: true,
            }
        );
    };

    const getStatusLabel = (status: string) => {
        const labels: Record<string, string> = {
            pending: 'Por iniciar',
            in_progress: 'Em curso',
            done: 'Concluída',
            cancelled: 'Cancelada',
            waiting: 'Aguardando',
            pending_validation: 'A aguardar validação',
            validated: 'Validada',
            reopened: 'Reaberta',
        };
        return labels[status] || status;
    };

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'pending':
                return 'bg-gray-100 text-gray-800';
            case 'in_progress':
                return 'bg-blue-100 text-blue-800';
            case 'done':
                return 'bg-green-100 text-green-800';
            case 'cancelled':
                return 'bg-red-100 text-red-800';
            case 'waiting':
                return 'bg-yellow-100 text-yellow-800';
            case 'pending_validation':
                return 'bg-purple-100 text-purple-800';
            case 'validated':
                return 'bg-green-100 text-green-800';
            case 'reopened':
                return 'bg-orange-100 text-orange-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    return (
        <>
            <Head title={task.title} />

            <MobileLayout title={task.title}>
                <div className="p-4 space-y-4">
                    {/* Status and Priority */}
                    <div className="bg-white p-4 rounded-lg border border-gray-200 space-y-3">
                        <div className="flex gap-2">
                            <span className={`px-3 py-1 rounded-full text-sm font-medium ${getStatusColor(task.status)}`}>
                                {getStatusLabel(task.status)}
                            </span>
                            <span className="px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                {task.priority}
                            </span>
                        </div>

                        {task.ticket && (
                            <div>
                                <p className="text-xs text-gray-500">Pedido/Ocorrência</p>
                                <p className="font-medium text-gray-900">#{task.ticket.reference}</p>
                            </div>
                        )}

                        {task.due_date && (
                            <div>
                                <p className="text-xs text-gray-500">Prazo</p>
                                <p className="font-medium text-gray-900">{new Date(task.due_date).toLocaleDateString('pt-PT')}</p>
                            </div>
                        )}
                    </div>

                    {/* Description */}
                    {task.description && (
                        <div className="bg-white p-4 rounded-lg border border-gray-200">
                            <h3 className="text-sm font-medium text-gray-700 mb-2">Descrição</h3>
                            <p className="text-gray-900">{task.description}</p>
                        </div>
                    )}

                    {/* Checklists */}
                    {task.checklists && task.checklists.length > 0 && (
                        <div className="bg-white p-4 rounded-lg border border-gray-200 space-y-3">
                            <h3 className="text-sm font-medium text-gray-700">Checklist</h3>
                            {task.checklists.map((checklist: TaskChecklist) => (
                                <div key={checklist.id} className="space-y-2">
                                    <label className="flex items-center gap-3 cursor-pointer">
                                        <input
                                            type="checkbox"
                                            checked={checklist.is_completed}
                                            onChange={e => handleChecklistToggle(checklist.id, e.target.checked)}
                                            className="rounded w-5 h-5"
                                        />
                                        <span className={checklist.is_completed ? 'line-through text-gray-400' : 'text-gray-900'}>
                                            {checklist.title}
                                        </span>
                                    </label>
                                </div>
                            ))}
                        </div>
                    )}

                    {/* Observations */}
                    {task.observations && (
                        <div className="bg-white p-4 rounded-lg border border-gray-200">
                            <h3 className="text-sm font-medium text-gray-700 mb-2">Observações</h3>
                            <p className="text-sm text-gray-600 whitespace-pre-wrap">{task.observations}</p>
                        </div>
                    )}

                    {/* Add Observation */}
                    <div className="bg-white p-4 rounded-lg border border-gray-200">
                        {!showObservationForm ? (
                            <button
                                onClick={() => setShowObservationForm(true)}
                                className="w-full px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors"
                            >
                                Adicionar Observação
                            </button>
                        ) : (
                            <form onSubmit={handleAddObservation} className="space-y-3">
                                <textarea
                                    value={observationText}
                                    onChange={e => setObservationText(e.target.value)}
                                    placeholder="Escreva sua observação..."
                                    rows={3}
                                    className="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    disabled={processing}
                                />
                                <div className="flex gap-2">
                                    <button
                                        type="submit"
                                        disabled={!observationText.trim() || processing}
                                        className="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors disabled:opacity-50"
                                    >
                                        {processing ? 'Enviando...' : 'Enviar'}
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => {
                                            setShowObservationForm(false);
                                            setObservationText('');
                                        }}
                                        className="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg text-sm font-medium hover:bg-gray-300 transition-colors"
                                    >
                                        Cancelar
                                    </button>
                                </div>
                            </form>
                        )}
                    </div>

                    {/* Attachments */}
                    <div className="bg-white p-4 rounded-lg border border-gray-200 space-y-3">
                        <h3 className="text-sm font-medium text-gray-700">Anexos ({task.attachments.length})</h3>

                        {task.attachments.length > 0 && (
                            <div className="space-y-2">
                                {task.attachments.map((attachment: Attachment) => (
                                    <a
                                        key={attachment.id}
                                        href={route('mobile.tasks.attachments.download', { task: task.id, attachment: attachment.id })}
                                        className="block px-3 py-2 bg-gray-50 rounded-lg border border-gray-200 hover:bg-gray-100 transition-colors text-sm"
                                    >
                                        <p className="font-medium text-gray-900 truncate">{attachment.file_name}</p>
                                        <p className="text-xs text-gray-500">
                                            {(attachment.size / 1024).toFixed(1)} KB
                                        </p>
                                    </a>
                                ))}
                            </div>
                        )}

                        {!showFileUpload ? (
                            <button
                                onClick={() => setShowFileUpload(true)}
                                className="w-full px-4 py-2 bg-gray-200 text-gray-800 rounded-lg text-sm font-medium hover:bg-gray-300 transition-colors"
                            >
                                + Anexar Ficheiro
                            </button>
                        ) : (
                            <form
                                onSubmit={handleFileUpload}
                                className="space-y-2"
                                encType="multipart/form-data"
                            >
                                <input
                                    type="file"
                                    name="file"
                                    className="block w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-100 file:text-blue-700"
                                    accept=".jpeg,.jpg,.png,.pdf,.doc,.docx,.xls,.xlsx"
                                    required
                                />
                                <div className="flex gap-2">
                                    <button
                                        type="submit"
                                        className="flex-1 px-4 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors"
                                    >
                                        Enviar
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => setShowFileUpload(false)}
                                        className="flex-1 px-4 py-2.5 bg-gray-200 text-gray-800 rounded-lg text-sm font-medium hover:bg-gray-300 transition-colors"
                                    >
                                        Cancelar
                                    </button>
                                </div>
                            </form>
                        )}
                    </div>

                    {/* Action Buttons */}
                    <div className="bg-white p-4 rounded-lg border border-gray-200 space-y-2">
                        {can.start && task.status === 'pending' && (
                            <button
                                onClick={handleStart}
                                disabled={processing}
                                className="w-full px-4 py-2.5 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition-colors disabled:opacity-50 text-sm"
                            >
                                {processing ? 'A iniciar...' : 'Iniciar Tarefa'}
                            </button>
                        )}

                        {can.complete && ['in_progress', 'waiting'].includes(task.status) && (
                            <button
                                onClick={handleComplete}
                                disabled={processing}
                                className="w-full px-4 py-2.5 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition-colors disabled:opacity-50 text-sm"
                            >
                                {processing ? 'A concluir...' : 'Concluir Tarefa'}
                            </button>
                        )}

                        {can.submitValidation && ['done', 'waiting'].includes(task.status) && (
                            <button
                                onClick={handleSubmitValidation}
                                disabled={processing}
                                className="w-full px-4 py-2.5 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition-colors disabled:opacity-50 text-sm"
                            >
                                {processing ? 'A enviar...' : 'Enviar para Validação'}
                            </button>
                        )}

                        {task.status === 'pending_validation' && (
                            <div className="px-4 py-2 bg-purple-50 text-purple-700 rounded-lg text-sm">
                                Aguardando validação...
                            </div>
                        )}

                        {task.status === 'validated' && (
                            <div className="px-4 py-2 bg-green-50 text-green-700 rounded-lg text-sm">
                                Tarefa validada com sucesso
                            </div>
                        )}

                        {can.reopen && task.status === 'reopened' && (
                            <div className="px-4 py-2 bg-orange-50 text-orange-700 rounded-lg text-sm">
                                Esta tarefa foi reaberta
                                {task.validation_notes && <p className="mt-1">{task.validation_notes}</p>}
                            </div>
                        )}
                    </div>
                </div>
            </MobileLayout>
        </>
    );
}
