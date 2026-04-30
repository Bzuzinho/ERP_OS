import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type Option = { id: number; name?: string; reference?: string; title?: string };

type Props = {
    statuses: string[];
    eventTypes: string[];
    visibilities: string[];
    tickets: Option[];
    contacts: Option[];
};

export default function Create({ statuses, eventTypes, visibilities, tickets, contacts }: Props) {
    const { data, setData, post, processing } = useForm({
        title: '',
        description: '',
        event_type: eventTypes[0] ?? 'appointment',
        status: statuses[0] ?? 'scheduled',
        visibility: visibilities[0] ?? 'public',
        start_at: '',
        end_at: '',
        location_text: '',
        related_ticket_id: '',
        related_contact_id: '',
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        post(route('admin.events.store'));
    };

    return (
        <AdminLayout title="Novo evento" subtitle="Registe reunioes, atendimentos e atividades na agenda da junta.">
            <Head title="Novo evento" />
            <form onSubmit={submit} className="space-y-4 rounded-2xl border border-slate-200 bg-white p-6">
                <h1 className="text-xl font-semibold text-slate-900">Criar evento</h1>
                <input value={data.title} onChange={(event) => setData('title', event.target.value)} placeholder="Titulo" className="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                <textarea value={data.description} onChange={(event) => setData('description', event.target.value)} placeholder="Descricao" rows={4} className="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                <div className="grid gap-3 md:grid-cols-3">
                    <select value={data.event_type} onChange={(event) => setData('event_type', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        {eventTypes.map((item) => <option key={item} value={item}>{item}</option>)}
                    </select>
                    <select value={data.status} onChange={(event) => setData('status', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        {statuses.map((item) => <option key={item} value={item}>{item}</option>)}
                    </select>
                    <select value={data.visibility} onChange={(event) => setData('visibility', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        {visibilities.map((item) => <option key={item} value={item}>{item}</option>)}
                    </select>
                </div>
                <div className="grid gap-3 md:grid-cols-2">
                    <input type="datetime-local" value={data.start_at} onChange={(event) => setData('start_at', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                    <input type="datetime-local" value={data.end_at} onChange={(event) => setData('end_at', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                </div>
                <input value={data.location_text} onChange={(event) => setData('location_text', event.target.value)} placeholder="Local" className="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                <div className="grid gap-3 md:grid-cols-2">
                    <select value={data.related_ticket_id} onChange={(event) => setData('related_ticket_id', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Ticket relacionado</option>
                        {tickets.map((ticket) => <option key={ticket.id} value={ticket.id}>{ticket.reference} - {ticket.title}</option>)}
                    </select>
                    <select value={data.related_contact_id} onChange={(event) => setData('related_contact_id', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Contacto relacionado</option>
                        {contacts.map((contact) => <option key={contact.id} value={contact.id}>{contact.name}</option>)}
                    </select>
                </div>
                <button disabled={processing} className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 disabled:opacity-60">Guardar</button>
            </form>
        </AdminLayout>
    );
}
