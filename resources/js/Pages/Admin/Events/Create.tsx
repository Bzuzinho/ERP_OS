import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';
import { FormEvent, useState } from 'react';

type Option = { id: number; name?: string; reference?: string; title?: string };

type ParticipantEntry = { user_id: string; contact_id: string; role: string };

type Props = {
    statuses: string[];
    eventTypes: string[];
    visibilities: string[];
    tickets: Option[];
    contacts: Option[];
    users: Option[];
};

export default function Create({ statuses, eventTypes, visibilities, tickets, contacts, users }: Props) {
    const { data, setData, post, processing } = useForm<{
        title: string;
        description: string;
        event_type: string;
        status: string;
        visibility: string;
        start_at: string;
        end_at: string;
        location_text: string;
        related_ticket_id: string;
        related_contact_id: string;
        participants: ParticipantEntry[];
    }>({
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
        participants: [],
    });

    const [newUserId, setNewUserId] = useState('');
    const [newContactId, setNewContactId] = useState('');
    const [newRole, setNewRole] = useState('');

    const addParticipant = () => {
        if (!newUserId && !newContactId) return;
        setData('participants', [...data.participants, { user_id: newUserId, contact_id: newContactId, role: newRole }]);
        setNewUserId('');
        setNewContactId('');
        setNewRole('');
    };

    const removeParticipant = (index: number) => {
        setData('participants', data.participants.filter((_, i) => i !== index));
    };

    const participantLabel = (p: ParticipantEntry) => {
        const user = users.find((u) => String(u.id) === p.user_id);
        const contact = contacts.find((c) => String(c.id) === p.contact_id);
        return user?.name ?? contact?.name ?? '—';
    };

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
                    {data.participants.length > 0 && (
                        <ul className="space-y-1">
                            {data.participants.map((p, i) => (
                                <li key={i} className="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2 text-sm">
                                    <span>{participantLabel(p)}{p.role ? ` — ${p.role}` : ''}</span>
                                    <button type="button" onClick={() => removeParticipant(i)} className="text-xs text-red-500 hover:underline">Remover</button>
                                </li>
                            ))}
                        </ul>
                    )}
                </div>

                <button disabled={processing} className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 disabled:opacity-60">Guardar</button>
            </form>
        </AdminLayout>
    );
}
