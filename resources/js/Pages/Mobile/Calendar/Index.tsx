import MobileLayout from '@/Layouts/MobileLayout';
import { type PageProps } from '@/types';

type PaginationLink = { url: string | null; label: string; active: boolean };

type PaginatedEvents = {
    data: Event[];
    links: PaginationLink[];
    total?: number;
};

type Event = {
    id: number;
    title: string;
    description: string | null;
    start_at: string;
    end_at: string;
    location_text: string | null;
    space?: { id: number; name: string };
    createdBy?: { id: number; name: string };
};

type CalendarIndexProps = {
    events: PaginatedEvents;
};

export default function CalendarIndex(props: PageProps<CalendarIndexProps>) {
    const { events } = props;

    return (
        <MobileLayout title="Agenda">
            <div className="p-4 space-y-4">
                {events.data && events.data.length > 0 ? (
                    <div className="space-y-3">
                        {events.data.map((event: Event) => (
                            <div key={event.id} className="bg-white p-4 rounded-lg border border-gray-200 hover:shadow-md transition-shadow">
                                <div className="space-y-2">
                                    <h3 className="font-medium text-gray-900">{event.title}</h3>

                                    <div className="space-y-1 text-sm text-gray-600">
                                        <p>
                                            <span className="font-medium">Data:</span>{' '}
                                            {new Date(event.start_at).toLocaleString('pt-PT', { weekday: 'long', day: 'numeric', month: 'long', hour: '2-digit', minute: '2-digit' })}
                                        </p>

                                        {event.space && (
                                            <p>
                                                <span className="font-medium">Local:</span> {event.space.name}
                                            </p>
                                        )}

                                        {event.location_text && (
                                            <p>
                                                <span className="font-medium">Localização:</span> {event.location_text}
                                            </p>
                                        )}
                                    </div>

                                    {event.description && <p className="text-sm text-gray-600 mt-2">{event.description}</p>}
                                </div>
                            </div>
                        ))}
                    </div>
                ) : (
                    <div className="text-center py-12">
                        <p className="text-gray-500">Nenhum evento agendado</p>
                    </div>
                )}
            </div>
        </MobileLayout>
    );
}
