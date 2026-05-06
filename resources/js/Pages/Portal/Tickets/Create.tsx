import PortalLayout from '@/Layouts/PortalLayout';
import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type Option = {
    id: number;
    name: string;
    slug?: string;
};

type Props = {
    contacts: Option[];
    themes: Option[];
};

export default function PortalTicketsCreate({ contacts, themes }: Props) {
    const form = useForm({
        contact_id: contacts[0] ? String(contacts[0].id) : '',
        title: '',
        description: '',
        category: '',
        subcategory: '',
        location_text: '',
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(route('portal.tickets.store'));
    };

    return (
        <PortalLayout title="Criar pedido" subtitle="Explique de forma simples o que pretende reportar ou solicitar.">
            <form onSubmit={submit} className="space-y-4 rounded-2xl border border-stone-200 bg-white p-4 shadow-sm sm:p-6">
                <div className="grid gap-4 md:grid-cols-2">
                    <input
                        value={form.data.title}
                        onChange={(event) => form.setData('title', event.target.value)}
                        placeholder="Assunto"
                        className="rounded-xl border border-stone-300 px-3 py-2 text-sm md:col-span-2"
                    />
                    <select
                        value={form.data.category}
                        onChange={(event) => form.setData('category', event.target.value)}
                        className="rounded-xl border border-stone-300 px-3 py-2 text-sm md:col-span-2"
                    >
                        <option value="">Tipo de pedido / Tema</option>
                        {themes.map((theme) => (
                            <option key={theme.id} value={theme.slug ?? theme.name}>
                                {theme.name}
                            </option>
                        ))}
                    </select>
                    <textarea
                        value={form.data.description}
                        onChange={(event) => form.setData('description', event.target.value)}
                        placeholder="Descricao"
                        className="min-h-28 rounded-xl border border-stone-300 px-3 py-2 text-sm md:col-span-2"
                    />
                    <input
                        value={form.data.location_text}
                        onChange={(event) => form.setData('location_text', event.target.value)}
                        placeholder="Localizacao / Morada"
                        className="rounded-xl border border-stone-300 px-3 py-2 text-sm md:col-span-2"
                    />
                    <select
                        value={form.data.contact_id}
                        onChange={(event) => form.setData('contact_id', event.target.value)}
                        className="rounded-xl border border-stone-300 px-3 py-2 text-sm md:col-span-2"
                    >
                        <option value="">Sem contacto associado</option>
                        {contacts.map((contact) => (
                            <option key={contact.id} value={contact.id}>
                                {contact.name}
                            </option>
                        ))}
                    </select>
                </div>

                <p className="text-xs text-slate-600">Pode acompanhar o estado deste pedido depois de submetido.</p>

                <div className="flex items-center gap-3">
                    <button type="submit" disabled={form.processing} className="rounded-xl bg-amber-600 px-4 py-2 text-sm font-medium text-white hover:bg-amber-500 disabled:opacity-50">
                        Submeter pedido
                    </button>
                    <Link href={route('portal.tickets.index')} className="text-sm text-stone-700 hover:text-stone-950">
                        Cancelar
                    </Link>
                </div>
            </form>
        </PortalLayout>
    );
}
