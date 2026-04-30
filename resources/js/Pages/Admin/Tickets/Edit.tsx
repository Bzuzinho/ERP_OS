import AdminLayout from '@/Layouts/AdminLayout';
import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type Option = { id: number; name: string };

type Ticket = {
    id: number;
    contact_id: number | null;
    assigned_to: number | null;
    category: string | null;
    subcategory: string | null;
    priority: string;
    title: string;
    description: string;
    location_text: string | null;
    source: string;
    visibility: string;
    due_date: string | null;
};

type Props = {
    ticket: Ticket;
    contacts: Option[];
    users: Option[];
    priorities: string[];
    sources: string[];
};

export default function TicketsEdit({ ticket, contacts, users, priorities, sources }: Props) {
    const form = useForm({
        contact_id: ticket.contact_id ? String(ticket.contact_id) : '',
        assigned_to: ticket.assigned_to ? String(ticket.assigned_to) : '',
        category: ticket.category ?? '',
        subcategory: ticket.subcategory ?? '',
        priority: ticket.priority,
        title: ticket.title,
        description: ticket.description,
        location_text: ticket.location_text ?? '',
        source: ticket.source,
        visibility: ticket.visibility,
        due_date: ticket.due_date ?? '',
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.put(route('admin.tickets.update', ticket.id));
    };

    return (
        <AdminLayout title="Editar Pedido" subtitle={ticket.title}>
            <form onSubmit={submit} className="space-y-4 rounded-2xl border border-slate-200 bg-white p-6">
                <div className="grid gap-4 md:grid-cols-2">
                    <input value={form.data.title} onChange={(event) => form.setData('title', event.target.value)} placeholder="Assunto" className="rounded-xl border border-slate-300 px-3 py-2 text-sm md:col-span-2" />
                    <textarea value={form.data.description} onChange={(event) => form.setData('description', event.target.value)} placeholder="Descricao" className="min-h-28 rounded-xl border border-slate-300 px-3 py-2 text-sm md:col-span-2" />
                    <input value={form.data.category} onChange={(event) => form.setData('category', event.target.value)} placeholder="Categoria" className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                    <input value={form.data.subcategory} onChange={(event) => form.setData('subcategory', event.target.value)} placeholder="Subcategoria" className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                    <select value={form.data.contact_id} onChange={(event) => form.setData('contact_id', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Sem contacto</option>
                        {contacts.map((item) => <option key={item.id} value={item.id}>{item.name}</option>)}
                    </select>
                    <select value={form.data.assigned_to} onChange={(event) => form.setData('assigned_to', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Sem responsavel</option>
                        {users.map((item) => <option key={item.id} value={item.id}>{item.name}</option>)}
                    </select>
                    <select value={form.data.priority} onChange={(event) => form.setData('priority', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        {priorities.map((item) => <option key={item} value={item}>{item}</option>)}
                    </select>
                    <select value={form.data.source} onChange={(event) => form.setData('source', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        {sources.map((item) => <option key={item} value={item}>{item}</option>)}
                    </select>
                    <select value={form.data.visibility} onChange={(event) => form.setData('visibility', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        <option value="internal">Interno</option>
                        <option value="public">Publico</option>
                    </select>
                    <input type="date" value={form.data.due_date} onChange={(event) => form.setData('due_date', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                    <input value={form.data.location_text} onChange={(event) => form.setData('location_text', event.target.value)} placeholder="Localizacao" className="rounded-xl border border-slate-300 px-3 py-2 text-sm md:col-span-2" />
                </div>

                <div className="flex items-center gap-3">
                    <button type="submit" disabled={form.processing} className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 disabled:opacity-50">Guardar</button>
                    <Link href={route('admin.tickets.show', ticket.id)} className="text-sm text-slate-700 hover:text-slate-950">Cancelar</Link>
                </div>
            </form>
        </AdminLayout>
    );
}
