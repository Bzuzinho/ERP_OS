import PortalLayout from '@/Layouts/PortalLayout';
import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type Option = {
    id: number;
    name: string;
};

type Props = {
    contacts: Option[];
    priorities: string[];
    sources: string[];
};

export default function PortalTicketsCreate({ contacts, priorities }: Props) {
    const form = useForm({
        contact_id: contacts[0] ? String(contacts[0].id) : '',
        priority: priorities[1] ?? 'normal',
        title: '',
        description: '',
        category: '',
        subcategory: '',
        location_text: '',
        source: 'portal',
        visibility: 'internal',
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(route('portal.tickets.store'));
    };

    return (
        <PortalLayout title="Novo pedido" subtitle="Submeta um pedido ao atendimento municipal">
            <form onSubmit={submit} className="space-y-4 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
                <div className="grid gap-4 md:grid-cols-2">
                    <input value={form.data.title} onChange={(event) => form.setData('title', event.target.value)} placeholder="Assunto" className="rounded-xl border border-stone-300 px-3 py-2 text-sm md:col-span-2" />
                    <textarea value={form.data.description} onChange={(event) => form.setData('description', event.target.value)} placeholder="Descreva o pedido" className="min-h-28 rounded-xl border border-stone-300 px-3 py-2 text-sm md:col-span-2" />
                    <select value={form.data.contact_id} onChange={(event) => form.setData('contact_id', event.target.value)} className="rounded-xl border border-stone-300 px-3 py-2 text-sm">
                        <option value="">Sem contacto associado</option>
                        {contacts.map((contact) => <option key={contact.id} value={contact.id}>{contact.name}</option>)}
                    </select>
                    <select value={form.data.priority} onChange={(event) => form.setData('priority', event.target.value)} className="rounded-xl border border-stone-300 px-3 py-2 text-sm">
                        {priorities.map((item) => <option key={item} value={item}>{item}</option>)}
                    </select>
                    <input value={form.data.category} onChange={(event) => form.setData('category', event.target.value)} placeholder="Categoria" className="rounded-xl border border-stone-300 px-3 py-2 text-sm" />
                    <input value={form.data.subcategory} onChange={(event) => form.setData('subcategory', event.target.value)} placeholder="Subcategoria" className="rounded-xl border border-stone-300 px-3 py-2 text-sm" />
                    <input value={form.data.location_text} onChange={(event) => form.setData('location_text', event.target.value)} placeholder="Localizacao" className="rounded-xl border border-stone-300 px-3 py-2 text-sm md:col-span-2" />
                </div>

                <div className="flex items-center gap-3">
                    <button type="submit" disabled={form.processing} className="rounded-xl bg-amber-600 px-4 py-2 text-sm font-medium text-white hover:bg-amber-500 disabled:opacity-50">Submeter</button>
                    <Link href={route('portal.tickets.index')} className="text-sm text-stone-700 hover:text-stone-950">Cancelar</Link>
                </div>
            </form>
        </PortalLayout>
    );
}
