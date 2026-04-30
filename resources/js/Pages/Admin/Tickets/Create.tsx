import AdminLayout from '@/Layouts/AdminLayout';
import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type Option = { id: number; name: string };

type Props = {
    contacts: Option[];
    users: Option[];
    statuses: string[];
    priorities: string[];
    sources: string[];
};

export default function TicketsCreate({ contacts, users, priorities, sources }: Props) {
    const form = useForm({
        contact_id: '',
        assigned_to: '',
        category: '',
        subcategory: '',
        priority: priorities[1] ?? 'normal',
        title: '',
        description: '',
        location_text: '',
        source: sources[1] ?? 'internal',
        visibility: 'internal',
        due_date: '',
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(route('admin.tickets.store'));
    };

    return (
        <AdminLayout title="Novo Pedido" subtitle="Registar um novo ticket no CRM municipal">
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
                    <Link href={route('admin.tickets.index')} className="text-sm text-slate-700 hover:text-slate-950">Cancelar</Link>
                </div>
            </form>
        </AdminLayout>
    );
}
