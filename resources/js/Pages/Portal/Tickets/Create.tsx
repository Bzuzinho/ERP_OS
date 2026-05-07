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
                    <div className="md:col-span-2">
                        <label className="mb-1 block text-sm font-medium text-stone-700">Assunto</label>
                        <input
                            value={form.data.title}
                            onChange={(event) => form.setData('title', event.target.value)}
                            placeholder="Ex.: Buraco na Rua Principal"
                            className="w-full rounded-xl border border-stone-300 px-3 py-3 text-sm"
                        />
                        {form.errors.title ? <p className="mt-1 text-xs text-rose-600">{form.errors.title}</p> : null}
                    </div>

                    <select
                        value={form.data.category}
                        onChange={(event) => form.setData('category', event.target.value)}
                        className="w-full rounded-xl border border-stone-300 px-3 py-3 text-sm md:col-span-2"
                    >
                        <option value="">Tipo de pedido / Tema</option>
                        {themes.map((theme) => (
                            <option key={theme.id} value={theme.slug ?? theme.name}>
                                {theme.name}
                            </option>
                        ))}
                    </select>
                    {form.errors.category ? <p className="-mt-2 text-xs text-rose-600 md:col-span-2">{form.errors.category}</p> : null}

                    <textarea
                        value={form.data.description}
                        onChange={(event) => form.setData('description', event.target.value)}
                        placeholder="Descreva o pedido com detalhe."
                        className="min-h-28 w-full rounded-xl border border-stone-300 px-3 py-3 text-sm md:col-span-2"
                    />
                    {form.errors.description ? <p className="-mt-2 text-xs text-rose-600 md:col-span-2">{form.errors.description}</p> : null}

                    <input
                        value={form.data.location_text}
                        onChange={(event) => form.setData('location_text', event.target.value)}
                        placeholder="Localizacao / Morada"
                        className="w-full rounded-xl border border-stone-300 px-3 py-3 text-sm md:col-span-2"
                    />
                    {form.errors.location_text ? <p className="-mt-2 text-xs text-rose-600 md:col-span-2">{form.errors.location_text}</p> : null}

                    <select
                        value={form.data.contact_id}
                        onChange={(event) => form.setData('contact_id', event.target.value)}
                        className="w-full rounded-xl border border-stone-300 px-3 py-3 text-sm md:col-span-2"
                    >
                        <option value="">Sem contacto associado</option>
                        {contacts.map((contact) => (
                            <option key={contact.id} value={contact.id}>
                                {contact.name}
                            </option>
                        ))}
                    </select>
                    {form.errors.contact_id ? <p className="-mt-2 text-xs text-rose-600 md:col-span-2">{form.errors.contact_id}</p> : null}
                </div>

                <p className="text-xs text-slate-600">Pode acompanhar o estado deste pedido depois de submetido.</p>

                <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <button type="submit" disabled={form.processing} className="w-full rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50 sm:w-auto">
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
