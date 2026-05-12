import MobileLayout from '@/Layouts/MobileLayout';
import { Link } from '@inertiajs/react';
import { type PageProps } from '@/types';

const ptDays = ['domingo', 'segunda-feira', 'terça-feira', 'quarta-feira', 'quinta-feira', 'sexta-feira', 'sábado'];
const ptMonths = ['janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];

function formatDatePt(dateString: string) {
    const d = new Date(dateString);
    return `${d.getDate()} ${ptMonths[d.getMonth()]}`;
}

function formatDayPt(dateString: string) {
    const d = new Date(dateString);
    return `${ptDays[d.getDay()]}, ${d.getDate()} de ${ptMonths[d.getMonth()]}`;
}

function formatTimePt(dateTimeString: string) {
    const d = new Date(dateTimeString);
    return d.toLocaleTimeString('pt-PT', { hour: '2-digit', minute: '2-digit' });
}

type Task = {
    id: number;
    title: string;
    status: string;
    priority: string;
    due_date: string | null;
    ticket?: { id: number; reference: string; title: string };
    assignee?: { id: number; name: string };
};

type Event = {
    id: number;
    title: string;
    start_at: string;
    space?: { id: number; name: string };
};

type Notification = {
    id: number;
    title: string;
    message: string;
    created_at: string;
};

type Today = {
    tasksForToday: Task[];
    overdueTasks: number;
    reopenedTasks: Task[];
    awaitingValidation: number;
    eventsToday: Event[];
    notifications: Notification[];
    today: string;
};

export default function TodayIndex(props: PageProps<Today>) {
    const {
        tasksForToday,
        overdueTasks,
        reopenedTasks,
        awaitingValidation,
        eventsToday,
        notifications,
        today,
    } = props;

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

    return (
        <MobileLayout title="Hoje" subtitle={formatDayPt(today)}>
            <div className="p-4 space-y-4">
                {/* Summary Cards */}
                <div className="grid grid-cols-2 gap-3">
                    <Link
                        href={route('mobile.tasks.index', { status: 'today' })}
                        className="bg-blue-50 p-4 rounded-lg border border-blue-200 hover:bg-blue-100 transition-colors"
                    >
                        <div className="text-2xl font-bold text-blue-600">{tasksForToday.length}</div>
                        <div className="text-sm text-blue-700">Tarefas hoje</div>
                    </Link>

                    {overdueTasks > 0 && (
                        <Link
                            href={route('mobile.tasks.index', { status: 'overdue' })}
                            className="bg-red-50 p-4 rounded-lg border border-red-200 hover:bg-red-100 transition-colors"
                        >
                            <div className="text-2xl font-bold text-red-600">{overdueTasks}</div>
                            <div className="text-sm text-red-700">Atrasadas</div>
                        </Link>
                    )}

                    {reopenedTasks.length > 0 && (
                        <Link
                            href={route('mobile.tasks.index', { status: 'reopened' })}
                            className="bg-yellow-50 p-4 rounded-lg border border-yellow-200 hover:bg-yellow-100 transition-colors"
                        >
                            <div className="text-2xl font-bold text-yellow-600">{reopenedTasks.length}</div>
                            <div className="text-sm text-yellow-700">Reabertas</div>
                        </Link>
                    )}

                    {awaitingValidation > 0 && (
                        <Link
                            href={route('mobile.tasks.index', { status: 'pending_validation' })}
                            className="bg-purple-50 p-4 rounded-lg border border-purple-200 hover:bg-purple-100 transition-colors"
                        >
                            <div className="text-2xl font-bold text-purple-600">{awaitingValidation}</div>
                            <div className="text-sm text-purple-700">A validar</div>
                        </Link>
                    )}
                </div>

                {/* Tasks for Today */}
                {tasksForToday.length > 0 && (
                    <div className="space-y-3">
                        <h2 className="text-lg font-semibold text-gray-900">Tarefas para hoje</h2>
                        {tasksForToday.map(task => (
                            <Link
                                key={task.id}
                                href={route('mobile.tasks.show', task.id)}
                                className="block bg-white p-4 rounded-lg border border-gray-200 hover:shadow-md transition-shadow"
                            >
                                <div className="flex items-start justify-between gap-3">
                                    <div className="flex-1 min-w-0">
                                        <h3 className="font-medium text-gray-900 truncate">{task.title}</h3>
                                        {task.ticket && (
                                            <p className="text-sm text-gray-500 mt-1">#{task.ticket.reference}</p>
                                        )}
                                    </div>
                                    <span
                                        className={`px-2 py-1 rounded text-xs font-medium whitespace-nowrap ${getPriorityColor(
                                            task.priority
                                        )}`}
                                    >
                                        {task.priority}
                                    </span>
                                </div>
                            </Link>
                        ))}
                    </div>
                )}

                {/* Events Today */}
                {eventsToday.length > 0 && (
                    <div className="space-y-3">
                        <h2 className="text-lg font-semibold text-gray-900">Eventos</h2>
                        {eventsToday.map(event => (
                            <div key={event.id} className="bg-white p-4 rounded-lg border border-gray-200">
                                <h3 className="font-medium text-gray-900">{event.title}</h3>
                                <p className="text-sm text-gray-500 mt-1">
                                    {formatTimePt(event.start_at)}
                                    {event.space && ` - ${event.space.name}`}
                                </p>
                            </div>
                        ))}
                    </div>
                )}

                {/* Notifications */}
                {notifications.length > 0 && (
                    <div className="space-y-3">
                        <h2 className="text-lg font-semibold text-gray-900">Alertas</h2>
                        {notifications.slice(0, 3).map(notification => (
                            <div key={notification.id} className="bg-blue-50 p-4 rounded-lg border border-blue-200">
                                <h4 className="font-medium text-blue-900">{notification.title}</h4>
                                <p className="text-sm text-blue-700 mt-1 line-clamp-2">{notification.message}</p>
                            </div>
                        ))}
                    </div>
                )}

                {tasksForToday.length === 0 && reopenedTasks.length === 0 && eventsToday.length === 0 && (
                    <div className="text-center py-12">
                        <p className="text-gray-500">Sem tarefas ou eventos para hoje</p>
                    </div>
                )}
            </div>
        </MobileLayout>
    );
}
