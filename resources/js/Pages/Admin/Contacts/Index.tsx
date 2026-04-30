import AdminLayout from '@/Layouts/AdminLayout';
import { Link, router } from '@inertiajs/react';

type Contact = {
    id: number;
    name: string;
    type: string;
    email: string | null;
    phone: string | null;
    is_active: boolean;
};

type Props = {
    contacts: {
        data: Contact[];
    };
    filters: {
        search?: string;
    };
};

export default function ContactsIndex({ contacts, filters }: Props) {
    return (
        <AdminLayout
            title="Contactos"
            subtitle="Gestao de cidadaos, associacoes, empresas e outras entidades"
            headerActions={
                <Link href={route('admin.contacts.create')} className="inline-flex rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                    Criar Contacto
                </Link>
            }
        >
            <div className="rounded-2xl border border-slate-200 bg-white p-4">
                <input
                    type="text"
                    defaultValue={filters.search ?? ''}
                    onChange={(event) =>
                        router.get(
                            route('admin.contacts.index'),
                            { search: event.target.value || undefined },
                            { preserveState: true, replace: true },
                        )
                    }
                    placeholder="Pesquisar por nome, email, telefone"
                    className="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"
                />
            </div>

            <div className="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white">
                <table className="min-w-full divide-y divide-slate-200 text-sm">
                    <thead className="bg-slate-50 text-left text-slate-600">
                        <tr>
                            <th className="px-4 py-3">Nome</th>
                            <th className="px-4 py-3">Tipo</th>
                            <th className="px-4 py-3">Email</th>
                            <th className="px-4 py-3">Telefone</th>
                            <th className="px-4 py-3">Estado</th>
                            <th className="px-4 py-3">Acoes</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                        {contacts.data.map((contact) => (
                            <tr key={contact.id}>
                                <td className="px-4 py-3 font-medium text-slate-900">{contact.name}</td>
                                <td className="px-4 py-3 text-slate-700">{contact.type}</td>
                                <td className="px-4 py-3 text-slate-700">{contact.email ?? '-'}</td>
                                <td className="px-4 py-3 text-slate-700">{contact.phone ?? '-'}</td>
                                <td className="px-4 py-3 text-slate-700">{contact.is_active ? 'Ativo' : 'Inativo'}</td>
                                <td className="px-4 py-3">
                                    <Link href={route('admin.contacts.show', contact.id)} className="text-slate-700 hover:text-slate-950">
                                        Ver
                                    </Link>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AdminLayout>
    );
}
