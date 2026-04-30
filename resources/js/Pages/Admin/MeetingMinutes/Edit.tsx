import AdminLayout from '@/Layouts/AdminLayout';
import { useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type Minute = { id: number; title: string; summary?: string | null; meeting_date: string; event_id?: number | null };
type Event = { id: number; title: string };
type Props = { meetingMinute: Minute; events: Event[] };

export default function MeetingMinuteEdit({ meetingMinute, events }: Props) {
    const form = useForm({
        event_id: meetingMinute.event_id?.toString() ?? '',
        title: meetingMinute.title,
        summary: meetingMinute.summary ?? '',
        meeting_date: meetingMinute.meeting_date.slice(0, 16),
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.put(route('admin.meeting-minutes.update', meetingMinute.id));
    };

    return (
        <AdminLayout title="Editar Ata" subtitle={meetingMinute.title}>
            <form onSubmit={submit} className="max-w-2xl space-y-5 rounded-2xl border border-slate-200 bg-white p-6">
                <div>
                    <label className="block text-sm font-medium text-slate-700">Evento</label>
                    <select value={form.data.event_id} onChange={(e) => form.setData('event_id', e.target.value)} className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        <option value="">-- Sem evento --</option>
                        {events.map((ev) => <option key={ev.id} value={ev.id}>{ev.title}</option>)}
                    </select>
                </div>
                <div>
                    <label className="block text-sm font-medium text-slate-700">Título *</label>
                    <input type="text" value={form.data.title} onChange={(e) => form.setData('title', e.target.value)} required className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                    {form.errors.title ? <p className="mt-1 text-xs text-rose-600">{form.errors.title}</p> : null}
                </div>
                <div>
                    <label className="block text-sm font-medium text-slate-700">Data da reunião</label>
                    <input type="datetime-local" value={form.data.meeting_date} onChange={(e) => form.setData('meeting_date', e.target.value)} className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                </div>
                <div>
                    <label className="block text-sm font-medium text-slate-700">Sumário</label>
                    <textarea value={form.data.summary} onChange={(e) => form.setData('summary', e.target.value)} rows={5} className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                </div>
                <div className="flex gap-3">
                    <button type="submit" disabled={form.processing} className="rounded-xl bg-slate-900 px-5 py-2 text-sm font-medium text-white hover:bg-slate-700 disabled:opacity-50">Guardar</button>
                    <a href={route('admin.meeting-minutes.show', meetingMinute.id)} className="rounded-xl border border-slate-300 px-5 py-2 text-sm text-slate-700 hover:bg-slate-50">Cancelar</a>
                </div>
            </form>
        </AdminLayout>
    );
}
