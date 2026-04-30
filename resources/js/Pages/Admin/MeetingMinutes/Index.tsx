import MeetingMinuteFilters from '@/Components/MeetingMinuteFilters';
import MeetingMinuteStatusBadge from '@/Components/MeetingMinuteStatusBadge';
import AdminLayout from '@/Layouts/AdminLayout';
import { Link } from '@inertiajs/react';

type Minute = {
    id: number;
    title: string;
    status: string;
    meeting_date: string;
    created_at: string;
    event?: { id: number; title: string } | null;
    creator?: { id: number; name: string } | null;
    approvedBy?: { id: number; name: string } | null;
};

type Props = {
    minutes: { data: Minute[] };
    filters: { search?: string; status?: string; eventId?: string };
    statuses: string[];
    events: { id: number; title: string }[];
};

export default function MeetingMinutesIndex({ minutes, filters, statuses, events }: Props) {
    return (
        <AdminLayout
            title="Atas de Reunião"
            subtitle="Registo das atas"
            headerActions={
                <Link href={route('admin.meeting-minutes.create')} className="inline-flex rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                    Criar Ata
                </Link>
            }
        >
            <MeetingMinuteFilters statuses={statuses} events={events} initialFilters={filters} />

            <div className="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white">
                <table className="min-w-full divide-y divide-slate-200 text-sm">
                    <thead className="bg-slate-50 text-left text-slate-600">
                        <tr>
                            <th className="px-4 py-3">Título</th>
                            <th className="px-4 py-3">Evento</th>
                            <th className="px-4 py-3">Estado</th>
                            <th className="px-4 py-3">Data reunião</th>
                            <th className="px-4 py-3">Aprovado por</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                        {minutes.data.map((m) => (
                            <tr key={m.id}>
                                <td className="px-4 py-3 font-medium text-slate-900">
                                    <Link href={route('admin.meeting-minutes.show', m.id)} className="hover:text-slate-700">{m.title}</Link>
                                </td>
                                <td className="px-4 py-3 text-slate-600">{m.event?.title ?? '-'}</td>
                                <td className="px-4 py-3"><MeetingMinuteStatusBadge status={m.status} /></td>
                                <td className="px-4 py-3 text-slate-700">{new Date(m.meeting_date).toLocaleDateString()}</td>
                                <td className="px-4 py-3 text-slate-600">{m.approvedBy?.name ?? '-'}</td>
                            </tr>
                        ))}
                        {minutes.data.length === 0 ? (
                            <tr><td colSpan={5} className="px-4 py-6 text-center text-slate-500">Sem atas.</td></tr>
                        ) : null}
                    </tbody>
                </table>
            </div>
        </AdminLayout>
    );
}
