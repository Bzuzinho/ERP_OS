import MeetingMinuteStatusBadge from '@/Components/MeetingMinuteStatusBadge';
import PortalLayout from '@/Layouts/PortalLayout';
import { Link } from '@inertiajs/react';

type Minute = {
    id: number;
    title: string;
    status: string;
    meeting_date: string;
    approved_at?: string | null;
    event?: { id: number; title: string } | null;
};

type Props = { meetingMinutes: { data: Minute[] } };

export default function PortalMeetingMinutesIndex({ meetingMinutes }: Props) {
    return (
        <PortalLayout title="Atas de Reunião">
            <div className="space-y-3">
                {meetingMinutes.data.map((m) => (
                    <div key={m.id} className="flex items-center justify-between gap-3 rounded-2xl border border-stone-200 bg-white p-4">
                        <div className="min-w-0 space-y-1">
                            <p className="truncate font-medium text-stone-900">{m.title}</p>
                            <div className="flex gap-2"><MeetingMinuteStatusBadge status={m.status} /></div>
                            <p className="text-xs text-stone-500">
                                {m.event?.title ? `${m.event.title} • ` : ''}
                                {new Date(m.meeting_date).toLocaleDateString()}
                            </p>
                        </div>
                        <Link href={route('portal.meeting-minutes.show', m.id)} className="shrink-0 rounded-xl border border-stone-300 px-3 py-2 text-sm text-stone-700 hover:bg-stone-50">
                            Ver
                        </Link>
                    </div>
                ))}
                {meetingMinutes.data.length === 0 ? (
                    <p className="rounded-2xl border border-stone-200 bg-white p-6 text-center text-stone-500">Sem atas disponíveis.</p>
                ) : null}
            </div>
        </PortalLayout>
    );
}
