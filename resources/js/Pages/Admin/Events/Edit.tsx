import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router, useForm } from '@inertiajs/react';
import { FormEvent, useState } from 'react';

type Option = { id: number; name?: string; reference?: string; title?: string };

type Participant = {
    id: number;
    role: string | null;
    attendance_status: string;
    user?: { id: number; name: string } | null;
    contact?: { id: number; name: string } | null;
};

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
        participants: Participant[];
    };
    eventTypes: string[];
    visibilities: string[];
    tickets: Option[];
    contacts: Option[];
    users: Option[];
};

export default function Edit({ event, eventTypes, visibilities, tickets, contacts, users }: Props) {
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

    const [newUserId, setNewUserId] = useState('');
    const [newContactId, setNewContactId] = useState('');
    const [newRole, setNewRole] = useState('');

    const addParticipant = () => {
        if (!newUserId && !newContactId) return;
        router.post(
            route('admin.events.participants.store', event.id),
            { user_id: newUserId || null, contact_id: newContactId || null, role: newRole || null },
            {
                preserveScroll: true,
                onSuccess: () => { setNewUserId(''); setNewContactId(''); setNewRole(''); },
            },
        );
    };

    const removeParticipant = (participantId: number) => {
        router.delete(route('admin.events.participants.destroy', { event: event.id, participant: participantId }), { preserveScroll: true });
    };

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

                <div className="space-y-3 rounded-xl border border-slate-200 p-4">
                    <h2 className="text-sm font-semibold text-slate-800">Participantes</h2>
                    <div className="grid gap-2 md:grid-cols-4">
                        <select value={newUserId} onChange={(e) => { setNewUserId(e.target.value); setNewContactId(''); }} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                            <option value="">Utilizador</option>
                            {users.map((u) => <option key={u.id} value={u.id}>{u.name}</option>)}
                        </select>
                        <select value={newContactId} onChange={(e) => { setNewContactId(e.target.value); setNewUserId(''); }} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                            <option value="">Contacto externo</option>
                            {contacts.map((c) => <option key={c.id} value={c.id}>{c.name}</option>)}
                        </select>
                        <input value={newRole} onChange={(e) => setNewRole(e.target.value)} placeholder="Papel (opcional)" className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                        <button type="button" onClick={addParticipant} className="rounded-xl bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Adicionar</button>
                    </div>
                    {event.participants.length > 0 && (
                        <ul className="space-y-1">
                            {event.participants.map((p) => (
                                <li key={p.id} className="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2 text-sm">
                                    <span>{p.user?.name ?? p.contact?.name ?? '—'}{p.role ? ` — ${p.role}` : ''}</span>
                                    <button type="button" onClick={() => removeParticipant(p.id)} className="text-xs text-red-500 hover:underline">Remover</button>
                                </li>
                            ))}
                        </ul>
                    )}
                    {event.participants.length === 0 && <p className="text-xs text-slate-400">Sem participantes.</p>}
                </div>

                <button disabled={processing} className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 disabled:opacity-60">Atualizar</button>
            </form>
        </AdminLayout>
    );
}
