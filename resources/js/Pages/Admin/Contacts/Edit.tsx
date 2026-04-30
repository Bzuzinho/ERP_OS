import AdminLayout from '@/Layouts/AdminLayout';
import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type Address = {
    id?: number;
    type?: string | null;
    address: string;
    postal_code?: string | null;
    locality?: string | null;
    municipality?: string | null;
    district?: string | null;
    is_primary?: boolean;
};

type Contact = {
    id: number;
    type: string;
    name: string;
    email: string | null;
    phone: string | null;
    mobile: string | null;
    nif: string | null;
    notes: string | null;
    user_id: number | null;
    is_active: boolean;
    addresses: Address[];
};

type UserOption = {
    id: number;
    name: string;
};

type Props = {
    contact: Contact;
    contactTypes: string[];
    users: UserOption[];
};

export default function ContactsEdit({ contact, contactTypes, users }: Props) {
    const form = useForm({
        type: contact.type,
        name: contact.name,
        email: contact.email ?? '',
        phone: contact.phone ?? '',
        mobile: contact.mobile ?? '',
        nif: contact.nif ?? '',
        notes: contact.notes ?? '',
        user_id: contact.user_id ? String(contact.user_id) : '',
        is_active: contact.is_active,
        addresses: contact.addresses.length > 0
            ? contact.addresses.map((address) => ({ ...address }))
            : [{ type: 'principal', address: '', postal_code: '', locality: '', municipality: '', district: '', is_primary: true }],
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.put(route('admin.contacts.update', contact.id));
    };

    const primaryAddress = form.data.addresses[0];

    return (
        <AdminLayout title="Editar Contacto" subtitle={`Atualizar dados de ${contact.name}`}>
            <form onSubmit={submit} className="space-y-4 rounded-2xl border border-slate-200 bg-white p-6">
                <div className="grid gap-4 md:grid-cols-2">
                    <input value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} placeholder="Nome" className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                    <select value={form.data.type} onChange={(event) => form.setData('type', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        {contactTypes.map((type) => <option key={type} value={type}>{type}</option>)}
                    </select>
                    <input value={form.data.email} onChange={(event) => form.setData('email', event.target.value)} placeholder="Email" className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                    <input value={form.data.phone} onChange={(event) => form.setData('phone', event.target.value)} placeholder="Telefone" className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                    <input value={form.data.mobile} onChange={(event) => form.setData('mobile', event.target.value)} placeholder="Telemovel" className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                    <input value={form.data.nif} onChange={(event) => form.setData('nif', event.target.value)} placeholder="NIF" className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                    <select value={form.data.user_id} onChange={(event) => form.setData('user_id', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        <option value="">Sem utilizador associado</option>
                        {users.map((user) => <option key={user.id} value={user.id}>{user.name}</option>)}
                    </select>
                </div>

                <textarea value={form.data.notes} onChange={(event) => form.setData('notes', event.target.value)} placeholder="Notas" className="min-h-24 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" />

                <div className="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <h3 className="text-sm font-semibold text-slate-900">Morada principal</h3>
                    <div className="mt-3 grid gap-3 md:grid-cols-2">
                        <input value={primaryAddress?.address ?? ''} onChange={(event) => form.setData('addresses', [{ ...primaryAddress, address: event.target.value } as Address])} placeholder="Morada" className="rounded-xl border border-slate-300 px-3 py-2 text-sm md:col-span-2" />
                        <input value={primaryAddress?.postal_code ?? ''} onChange={(event) => form.setData('addresses', [{ ...primaryAddress, postal_code: event.target.value } as Address])} placeholder="Codigo postal" className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                        <input value={primaryAddress?.locality ?? ''} onChange={(event) => form.setData('addresses', [{ ...primaryAddress, locality: event.target.value } as Address])} placeholder="Localidade" className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                        <input value={primaryAddress?.municipality ?? ''} onChange={(event) => form.setData('addresses', [{ ...primaryAddress, municipality: event.target.value } as Address])} placeholder="Municipio" className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                        <input value={primaryAddress?.district ?? ''} onChange={(event) => form.setData('addresses', [{ ...primaryAddress, district: event.target.value } as Address])} placeholder="Distrito" className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                    </div>
                </div>

                <div className="flex items-center gap-3">
                    <button type="submit" disabled={form.processing} className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 disabled:opacity-50">Guardar</button>
                    <Link href={route('admin.contacts.show', contact.id)} className="text-sm text-slate-700 hover:text-slate-950">Cancelar</Link>
                </div>
            </form>
        </AdminLayout>
    );
}
