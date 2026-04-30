import MeetingMinuteStatusBadge from '@/Components/MeetingMinuteStatusBadge';
import AdminLayout from '@/Layouts/AdminLayout';
import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type Minute = {
    id: number;
    title: string;
    summary?: string | null;
    status: string;
    meeting_date: string;
    approved_at?: string | null;
    event?: { id: number; title: string; start_at: string } | null;
    creator?: { id: number; name: string } | null;
    approvedBy?: { id: number; name: string } | null;
    document?: { id: number; title: string } | null;
};

type Props = {
    meetingMinute: Minute;
    can: { update: boolean; approve: boolean; delete: boolean };
};

export default function MeetingMinuteShow({ meetingMinute: minute, can }: Props) {
    const approveForm = useForm({});
    const handleApprove = (event: FormEvent) => {
        event.preventDefault();
        approveForm.post(route('admin.meeting-minutes.approve', minute.id));
    };

    return (
        <AdminLayout
            title={minute.title}
            subtitle={`Ata de Reunião`}
            headerActions={
                <div className="flex gap-3">
                    {can.update ? (
                        <Link href={route('admin.meeting-minutes.edit', minute.id)} className="inline-flex rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                            Editar
                        </Link>
                    ) : null}
                    {can.approve && minute.status !== 'approved' ? (
                        <form onSubmit={handleApprove}>
                            <button type="submit" className="inline-flex rounded-xl bg-emerald-700 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-600">
                                Aprovar
                            </button>
                        </form>
                    ) : null}
                </div>
            }
        >
            <div className="max-w-3xl space-y-4">
                <section className="rounded-2xl border border-slate-200 bg-white p-5 space-y-4">
                    <div className="flex flex-wrap gap-2">
                        <MeetingMinuteStatusBadge status={minute.status} />
                    </div>

                    <div className="grid gap-3 text-sm text-slate-600 md:grid-cols-2">
                        <p><span className="font-medium">Data da reunião:</span> {new Date(minute.meeting_date).toLocaleString()}</p>
                        <p><span className="font-medium">Criado por:</span> {minute.creator?.name ?? '-'}</p>
                        {minute.approved_at ? (
                            <p><span className="font-medium">Aprovado em:</span> {new Date(minute.approved_at).toLocaleString()}</p>
                        ) : null}
                        {minute.approvedBy ? (
                            <p><span className="font-medium">Aprovado por:</span> {minute.approvedBy.name}</p>
                        ) : null}
                    </div>

                    {minute.event ? (
                        <div className="rounded-xl bg-slate-50 p-3 text-sm">
                            <p className="font-medium text-slate-900">Evento: {minute.event.title}</p>
                            <p className="text-slate-600">{new Date(minute.event.start_at).toLocaleString()}</p>
                        </div>
                    ) : null}

                    {minute.summary ? (
                        <div>
                            <h3 className="mb-1 text-sm font-medium text-slate-700">Sumário</h3>
                            <p className="text-sm text-slate-600 whitespace-pre-wrap">{minute.summary}</p>
                        </div>
                    ) : null}

                    {minute.document ? (
                        <div className="rounded-xl border border-slate-200 p-3 text-sm">
                            <p className="font-medium text-slate-900">Documento associado:</p>
                            <Link href={route('admin.documents.show', minute.document.id)} className="text-slate-700 hover:text-slate-900">
                                {minute.document.title}
                            </Link>
                        </div>
                    ) : null}
                </section>
            </div>
        </AdminLayout>
    );
}
