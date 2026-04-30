import AdminLayout from '@/Layouts/AdminLayout';
import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type UserOption = {
    id: number;
    name: string;
};

type Props = {
    contactTypes: string[];
    users: UserOption[];
};

export default function ContactsCreate({ contactTypes, users }: Props) {
    const form = useForm({
        type: contactTypes[0] ?? 'citizen',
        name: '',
        email: '',
        phone: '',
        mobile: '',
        nif: '',
        notes: '',
        user_id: '',
        is_active: true,
        addresses: [
            {
                type: 'principal',
                address: '',
                postal_code: '',
                locality: '',
                municipality: '',
                district: '',
                is_primary: true,
            },
        ],
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(route('admin.contacts.store'));
    };

    return (
        <AdminLayout title="Novo Contacto" subtitle="Registo base de contacto para CRM municipal">
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
                        <input value={form.data.addresses[0].address} onChange={(event) => form.setData('addresses', [{ ...form.data.addresses[0], address: event.target.value }])} placeholder="Morada" className="rounded-xl border border-slate-300 px-3 py-2 text-sm md:col-span-2" />
                        <input value={form.data.addresses[0].postal_code} onChange={(event) => form.setData('addresses', [{ ...form.data.addresses[0], postal_code: event.target.value }])} placeholder="Codigo postal" className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                        <input value={form.data.addresses[0].locality} onChange={(event) => form.setData('addresses', [{ ...form.data.addresses[0], locality: event.target.value }])} placeholder="Localidade" className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                        <input value={form.data.addresses[0].municipality} onChange={(event) => form.setData('addresses', [{ ...form.data.addresses[0], municipality: event.target.value }])} placeholder="Municipio" className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                        <input value={form.data.addresses[0].district} onChange={(event) => form.setData('addresses', [{ ...form.data.addresses[0], district: event.target.value }])} placeholder="Distrito" className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                    </div>
                </div>

                <div className="flex items-center gap-3">
                    <button type="submit" disabled={form.processing} className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 disabled:opacity-50">Guardar</button>
                    <Link href={route('admin.contacts.index')} className="text-sm text-slate-700 hover:text-slate-950">Cancelar</Link>
                </div>
            </form>
        </AdminLayout>
    );
}
