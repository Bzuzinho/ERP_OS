import MeetingMinuteStatusBadge from '@/Components/MeetingMinuteStatusBadge';
import PortalLayout from '@/Layouts/PortalLayout';
import { Link } from '@inertiajs/react';

type Minute = {
    id: number;
    title: string;
    summary?: string | null;
    status: string;
    meeting_date: string;
    approved_at?: string | null;
    event?: { id: number; title: string; start_at: string } | null;
    document?: { id: number; title: string; can_download: boolean } | null;
};

type Props = { meetingMinute: Minute };

export default function PortalMeetingMinuteShow({ meetingMinute: minute }: Props) {
    return (
        <PortalLayout title={minute.title}>
            <div className="max-w-2xl rounded-2xl border border-stone-200 bg-white p-6 space-y-4">
                <div className="flex gap-2">
                    <MeetingMinuteStatusBadge status={minute.status} />
                </div>

                <dl className="grid gap-2 text-sm text-stone-600 md:grid-cols-2">
                    <div><dt className="font-medium">Data da reunião</dt><dd>{new Date(minute.meeting_date).toLocaleString()}</dd></div>
                    {minute.approved_at ? <div><dt className="font-medium">Aprovado em</dt><dd>{new Date(minute.approved_at).toLocaleString()}</dd></div> : null}
                </dl>

                {minute.event ? (
                    <div className="rounded-xl bg-stone-50 p-3 text-sm">
                        <p className="font-medium text-stone-900">Evento: {minute.event.title}</p>
                        <p className="text-stone-600">{new Date(minute.event.start_at).toLocaleString()}</p>
                    </div>
                ) : null}

                {minute.summary ? (
                    <div>
                        <h3 className="mb-1 text-sm font-medium text-stone-700">Sumário</h3>
                        <p className="text-sm text-stone-600 whitespace-pre-wrap">{minute.summary}</p>
                    </div>
                ) : null}

                {minute.document ? (
                    <div className="flex items-center justify-between rounded-xl border border-stone-200 p-3 text-sm">
                        <span className="font-medium text-stone-800">{minute.document.title}</span>
                        {minute.document.can_download ? (
                            <a href={route('portal.documents.download', minute.document.id)} className="rounded-xl bg-amber-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-amber-500">
                                Download
                            </a>
                        ) : null}
                    </div>
                ) : null}

                <Link href={route('portal.meeting-minutes.index')} className="inline-flex text-sm text-stone-600 hover:text-stone-900">← Voltar</Link>
            </div>
        </PortalLayout>
    );
}
