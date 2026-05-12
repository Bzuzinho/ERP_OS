import MobileLayout from '@/Layouts/MobileLayout';
import { type PageProps } from '@/types';

type PaginationLink = { url: string | null; label: string; active: boolean };

type PaginatedNotifications = {
    data: Notification[];
    links: PaginationLink[];
    total?: number;
};

type Notification = {
    id: number;
    type: string;
    title: string;
    message: string;
    priority: string;
    created_at: string;
    data: any;
};

type CommunicationsIndexProps = {
    notifications: PaginatedNotifications;
};

export default function CommunicationsIndex(props: PageProps<CommunicationsIndexProps>) {
    const { notifications } = props;

    const getPriorityColor = (priority: string) => {
        switch (priority) {
            case 'high':
                return 'bg-red-100 text-red-800';
            case 'medium':
                return 'bg-yellow-100 text-yellow-800';
            case 'low':
                return 'bg-green-100 text-green-800';
            default:
                return 'bg-blue-100 text-blue-800';
        }
    };

    return (
        <MobileLayout title="Comunicações">
            <div className="p-4 space-y-4">
                {notifications.data && notifications.data.length > 0 ? (
                    <div className="space-y-3">
                        {notifications.data.map((notification: Notification) => (
                            <div
                                key={notification.id}
                                className="bg-white p-4 rounded-lg border border-gray-200 hover:shadow-md transition-shadow"
                            >
                                <div className="space-y-2">
                                    <div className="flex items-start justify-between gap-3">
                                        <h3 className="font-medium text-gray-900 flex-1">{notification.title}</h3>
                                        {notification.priority && (
                                            <span className={`px-2 py-1 rounded text-xs font-medium whitespace-nowrap ${getPriorityColor(notification.priority)}`}>
                                                {notification.priority}
                                            </span>
                                        )}
                                    </div>

                                    <p className="text-sm text-gray-600 line-clamp-3">{notification.message}</p>

                                    <p className="text-xs text-gray-500 mt-2">
                                        {new Date(notification.created_at).toLocaleDateString('pt-PT')}
                                    </p>
                                </div>
                            </div>
                        ))}
                    </div>
                ) : (
                    <div className="text-center py-12">
                        <p className="text-gray-500">Nenhuma notificação</p>
                    </div>
                )}
            </div>
        </MobileLayout>
    );
}
