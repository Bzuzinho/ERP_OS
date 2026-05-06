import AdminLayout from '@/Layouts/AdminLayout';
import { Link, router, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type UserOption = {
    id: number;
    name: string;
    email: string;
};

type Props = {
    serviceArea: {
        id: number;
        name: string;
        slug: string;
        description: string | null;
        users: UserOption[];
        tickets: { id: number; reference: string; title: string; status: string }[];
    };
    availableUsers: UserOption[];
};

export default function ServiceAreasShow({ serviceArea, availableUsers }: Props) {
    const form = useForm({
        user_id: '',
        role: '',
        is_primary: false,
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(route('admin.service-areas.users.store', serviceArea.id), {
            onSuccess: () => form.reset('user_id', 'role', 'is_primary'),
        });
    };

    return (
        <AdminLayout
            title={serviceArea.name}
            subtitle="Gestao da area funcional"
            headerActions={
                <div className="flex items-center gap-2">
                    <Link href={route('admin.service-areas.edit', serviceArea.id)} className="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Editar</Link>
                    <button
                        type="button"
                        onClick={() => {
                            if (confirm('Remover esta area funcional?')) {
                                router.delete(route('admin.service-areas.destroy', serviceArea.id));
                            }
                        }}
                        className="rounded-xl border border-rose-200 bg-rose-50 px-3 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-100"
                    >
                        Remover
                    </button>
                </div>
            }
        >
            <div className="grid gap-4 lg:grid-cols-2">
                <section className="space-y-4 rounded-2xl border border-slate-200 bg-white p-4">
                    <h3 className="text-sm font-semibold text-slate-900">Utilizadores associados</h3>
                    <form onSubmit={submit} className="grid gap-2 md:grid-cols-3">
                        <select value={form.data.user_id} onChange={(event) => form.setData('user_id', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm md:col-span-2">
                            <option value="">Selecionar utilizador</option>
                            {availableUsers.map((user) => <option key={user.id} value={user.id}>{user.name}</option>)}
                        </select>
                        <button type="submit" className="rounded-xl bg-slate-900 px-3 py-2 text-sm font-semibold text-white">Associar</button>
                    </form>

                    <div className="space-y-2">
                        {serviceArea.users.length ? serviceArea.users.map((user) => (
                            <div key={user.id} className="flex items-center justify-between rounded-xl border border-slate-200 px-3 py-2">
                                <div>
                                    <p className="text-sm font-semibold text-slate-900">{user.name}</p>
                                    <p className="text-xs text-slate-500">{user.email}</p>
                                </div>
                                <button
                                    type="button"
                                    onClick={() => router.delete(route('admin.service-areas.users.destroy', { serviceArea: serviceArea.id, userId: user.id }))}
                                    className="text-xs font-semibold text-rose-700 hover:text-rose-900"
                                >
                                    Remover
                                </button>
                            </div>
                        )) : <p className="text-sm text-slate-500">Ainda sem utilizadores associados.</p>}
                    </div>
                </section>

                <section className="space-y-3 rounded-2xl border border-slate-200 bg-white p-4">
                    <h3 className="text-sm font-semibold text-slate-900">Pedidos associados</h3>
                    <div className="space-y-2">
                        {serviceArea.tickets.length ? serviceArea.tickets.map((ticket) => (
                            <Link key={ticket.id} href={route('admin.tickets.show', ticket.id)} className="block rounded-xl border border-slate-200 px-3 py-2 hover:bg-slate-50">
                                <p className="text-sm font-semibold text-slate-900">{ticket.reference} - {ticket.title}</p>
                                <p className="text-xs text-slate-500">Estado: {ticket.status}</p>
                            </Link>
                        )) : <p className="text-sm text-slate-500">Sem pedidos associados.</p>}
                    </div>
                </section>
            </div>
        </AdminLayout>
    );
}
