import AdminLayout from '@/Layouts/AdminLayout';
import { Link } from '@inertiajs/react';

type Address = {
    id: number;
    address: string;
    postal_code: string | null;
    locality: string | null;
    municipality: string | null;
    district: string | null;
    is_primary: boolean;
};

type Ticket = {
    id: number;
    reference: string;
    title: string;
    status: string;
    priority: string;
};

type Contact = {
    id: number;
    name: string;
    type: string;
    nif: string | null;
    email: string | null;
    phone: string | null;
    mobile: string | null;
    notes: string | null;
    is_active: boolean;
    addresses: Address[];
    tickets: Ticket[];
};

type Props = {
    contact: Contact;
};

export default function ContactsShow({ contact }: Props) {
    return (
        <AdminLayout
            title={contact.name}
            subtitle="Detalhe do contacto e pedidos associados"
            headerActions={
                <Link href={route('admin.contacts.edit', contact.id)} className="inline-flex rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                    Editar Contacto
                </Link>
            }
        >
            <div className="grid gap-4 lg:grid-cols-2">
                <section className="rounded-2xl border border-slate-200 bg-white p-5">
                    <h3 className="text-lg font-semibold text-slate-900">Dados principais</h3>
                    <dl className="mt-3 space-y-2 text-sm text-slate-700">
                        <div><dt className="font-medium">Tipo</dt><dd>{contact.type}</dd></div>
                        <div><dt className="font-medium">NIF</dt><dd>{contact.nif ?? '-'}</dd></div>
                        <div><dt className="font-medium">Email</dt><dd>{contact.email ?? '-'}</dd></div>
                        <div><dt className="font-medium">Telefone</dt><dd>{contact.phone ?? '-'}</dd></div>
                        <div><dt className="font-medium">Telemovel</dt><dd>{contact.mobile ?? '-'}</dd></div>
                        <div><dt className="font-medium">Estado</dt><dd>{contact.is_active ? 'Ativo' : 'Inativo'}</dd></div>
                    </dl>
                </section>

                <section className="rounded-2xl border border-slate-200 bg-white p-5">
                    <h3 className="text-lg font-semibold text-slate-900">Moradas</h3>
                    <ul className="mt-3 space-y-2 text-sm text-slate-700">
                        {contact.addresses.map((address) => (
                            <li key={address.id} className="rounded-xl border border-slate-200 bg-slate-50 p-3">
                                <p>{address.address}</p>
                                <p>{address.postal_code ?? ''} {address.locality ?? ''}</p>
                                <p>{address.municipality ?? ''} {address.district ?? ''}</p>
                            </li>
                        ))}
                        {contact.addresses.length === 0 ? <li>Sem moradas.</li> : null}
                    </ul>
                </section>
            </div>

            <section className="mt-4 rounded-2xl border border-slate-200 bg-white p-5">
                <h3 className="text-lg font-semibold text-slate-900">Pedidos associados</h3>
                <ul className="mt-3 space-y-2 text-sm">
                    {contact.tickets.map((ticket) => (
                        <li key={ticket.id} className="rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <Link href={route('admin.tickets.show', ticket.id)} className="font-medium text-slate-900 hover:text-slate-700">
                                {ticket.reference} - {ticket.title}
                            </Link>
                        </li>
                    ))}
                    {contact.tickets.length === 0 ? <li>Nenhum pedido associado.</li> : null}
                </ul>
            </section>
        </AdminLayout>
    );
}
