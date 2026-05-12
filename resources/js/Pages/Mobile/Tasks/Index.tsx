import MobileLayout from '@/Layouts/MobileLayout';
import { Link, router } from '@inertiajs/react';
import { type PageProps } from '@/types';

type PaginationLink = { url: string | null; label: string; active: boolean };

type PaginatedTasks = {
    data: Task[];
    links: PaginationLink[];
    meta?: { current_page: number; last_page: number; total: number };
    total?: number;
};

type Task = {
    id: number;
    title: string;
    status: string;
    priority: string;
    due_date: string | null;
    ticket?: { id: number; reference: string; title: string };
};

type TasksIndexProps = {
    tasks: PaginatedTasks;
    filters: { status: string };
    statuses: string[];
};

export default function TasksIndex(props: PageProps<TasksIndexProps>) {
    const { tasks, filters, statuses } = props;

    const filterOptions = [
        { value: '', label: 'Todas' },
        { value: 'today', label: 'Hoje' },
        { value: 'overdue', label: 'Atrasadas' },
        { value: 'pending', label: 'Por iniciar' },
        { value: 'in_progress', label: 'Em curso' },
        { value: 'reopened', label: 'Reabertas' },
        { value: 'pending_validation', label: 'Por validar' },
        { value: 'done', label: 'Concluídas' },
    ];

    const getPriorityColor = (priority: string) => {
        switch (priority) {
            case 'urgent':
                return 'bg-red-100 text-red-800';
            case 'high':
                return 'bg-orange-100 text-orange-800';
            case 'normal':
                return 'bg-blue-100 text-blue-800';
            case 'low':
                return 'bg-green-100 text-green-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
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

    const handleFilterChange = (status: string) => {
        router.get(route('mobile.tasks.index', { status: status || undefined }), {}, { replace: true });
    };

    return (
        <MobileLayout title="Minhas Tarefas">
            <div className="p-4 space-y-4">
                {/* Filter Buttons */}
                <div className="space-y-2">
                    <label className="text-sm font-medium text-gray-700">Filtro:</label>
                    <div className="flex flex-wrap gap-2">
                        {filterOptions.map(option => (
                            <button
                                key={option.value}
                                onClick={() => handleFilterChange(option.value)}
                                className={`px-3 py-1.5 rounded-full text-sm font-medium transition-colors ${
                                    filters.status === option.value
                                        ? 'bg-blue-600 text-white'
                                        : 'bg-gray-200 text-gray-800 hover:bg-gray-300'
                                }`}
                            >
                                {option.label}
                            </button>
                        ))}
                    </div>
                </div>

                {/* Tasks List */}
                {tasks.data && tasks.data.length > 0 ? (
                    <div className="space-y-3">
                        {tasks.data.map((task: Task) => (
                            <Link
                                key={task.id}
                                href={route('mobile.tasks.show', task.id)}
                                className="block bg-white p-4 rounded-lg border border-gray-200 hover:shadow-md transition-shadow"
                            >
                                <div className="space-y-2">
                                    <div className="flex items-start justify-between gap-3">
                                        <h3 className="font-medium text-gray-900 flex-1">{task.title}</h3>
                                        <span className={`px-2 py-1 rounded text-xs font-medium whitespace-nowrap ${getPriorityColor(task.priority)}`}>
                                            {task.priority}
                                        </span>
                                    </div>

                                    <div className="flex items-center justify-between">
                                        <div className="flex items-center gap-2">
                                            {task.ticket && (
                                                <span className="text-xs text-gray-500">#{task.ticket.reference}</span>
                                            )}
                                        </div>
                                        <span className={`px-2 py-0.5 rounded text-xs font-medium ${getStatusColor(task.status)}`}>
                                            {task.status === 'pending' && 'Por iniciar'}
                                            {task.status === 'in_progress' && 'Em curso'}
                                            {task.status === 'done' && 'Concluída'}
                                            {task.status === 'cancelled' && 'Cancelada'}
                                            {task.status === 'waiting' && 'Aguardando'}
                                            {task.status === 'pending_validation' && 'A validar'}
                                            {task.status === 'validated' && 'Validada'}
                                            {task.status === 'reopened' && 'Reaberta'}
                                        </span>
                                    </div>

                                    {task.due_date && (
                                        <p className="text-xs text-gray-500">Prazo: {new Date(task.due_date).toLocaleDateString('pt-PT')}</p>
                                    )}
                                </div>
                            </Link>
                        ))}
                    </div>
                ) : (
                    <div className="text-center py-12">
                        <p className="text-gray-500">Nenhuma tarefa encontrada</p>
                    </div>
                )}

                {/* Pagination */}
                {tasks.links && tasks.links.length > 0 && (
                    <div className="flex justify-center gap-2">
                        {tasks.links.map((link: PaginationLink, index: number) => (
                            <a
                                key={index}
                                href={link.url || '#'}
                                className={`px-3 py-1 rounded text-sm ${
                                    link.active ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-800 hover:bg-gray-300'
                                } ${!link.url ? 'opacity-50 cursor-not-allowed' : ''}`}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        ))}
                    </div>
                )}
            </div>
        </MobileLayout>
    );
}
