import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type Option = { id: number; name?: string; reference?: string; title?: string };

type Props = {
    event: {
        id: number;
        title: string;
        description?: string | null;
        event_type: string;
        visibility: string;
        start_at: string;
        end_at: string;
        location_text?: string | null;
        related_ticket_id?: number | null;
        related_contact_id?: number | null;
    };
    eventTypes: string[];
    visibilities: string[];
    tickets: Option[];
    contacts: Option[];
};

export default function Edit({ event, eventTypes, visibilities, tickets, contacts }: Props) {
    const { data, setData, patch, processing } = useForm({
        title: event.title,
        description: event.description ?? '',
        event_type: event.event_type,
        visibility: event.visibility,
        start_at: event.start_at.slice(0, 16),
        end_at: event.end_at.slice(0, 16),
        location_text: event.location_text ?? '',
        related_ticket_id: event.related_ticket_id ? String(event.related_ticket_id) : '',
        related_contact_id: event.related_contact_id ? String(event.related_contact_id) : '',
    });

    const submit = (evt: FormEvent) => {
        evt.preventDefault();
        patch(route('admin.events.update', event.id));
    };

    return (
        <AdminLayout title="Editar evento" subtitle="Atualize dados da agenda e relacoes com ticket/contacto.">
            <Head title="Editar evento" />
            <form onSubmit={submit} className="space-y-4 rounded-2xl border border-slate-200 bg-white p-6">
                <h1 className="text-xl font-semibold text-slate-900">Editar evento</h1>
                <input value={data.title} onChange={(evt) => setData('title', evt.target.value)} className="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                <textarea value={data.description} onChange={(evt) => setData('description', evt.target.value)} rows={4} className="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                <div className="grid gap-3 md:grid-cols-3">
                    <select value={data.event_type} onChange={(evt) => setData('event_type', evt.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        {eventTypes.map((item) => <option key={item} value={item}>{item}</option>)}
                    </select>
                    <select value={data.visibility} onChange={(evt) => setData('visibility', evt.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        {visibilities.map((item) => <option key={item} value={item}>{item}</option>)}
                    </select>
                    <input value={data.location_text} onChange={(evt) => setData('location_text', evt.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                </div>
                <div className="grid gap-3 md:grid-cols-2">
                    <input type="datetime-local" value={data.start_at} onChange={(evt) => setData('start_at', evt.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                    <input type="datetime-local" value={data.end_at} onChange={(evt) => setData('end_at', evt.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                </div>
                <div className="grid gap-3 md:grid-cols-2">
                    <select value={data.related_ticket_id} onChange={(evt) => setData('related_ticket_id', evt.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Ticket relacionado</option>
                        {tickets.map((ticket) => <option key={ticket.id} value={ticket.id}>{ticket.reference} - {ticket.title}</option>)}
                    </select>
                    <select value={data.related_contact_id} onChange={(evt) => setData('related_contact_id', evt.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Contacto relacionado</option>
                        {contacts.map((contact) => <option key={contact.id} value={contact.id}>{contact.name}</option>)}
                    </select>
                </div>
                <button disabled={processing} className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 disabled:opacity-60">Atualizar</button>
            </form>
        </AdminLayout>
    );
}
