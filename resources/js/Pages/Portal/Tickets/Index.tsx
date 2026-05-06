import AppCard from '@/Components/App/AppCard';
import EmptyState from '@/Components/App/EmptyState';
import FilterPills from '@/Components/App/FilterPills';
import FloatingActionButton from '@/Components/App/FloatingActionButton';
import PublicTicketStatusBadge from '@/Components/Portal/PublicTicketStatusBadge';
import { mapTicketStatusToPublicFilter } from '@/Components/Portal/publicTicketStatus';
import PortalLayout from '@/Layouts/PortalLayout';
import { Link } from '@inertiajs/react';
import { useMemo, useState } from 'react';

type TicketItem = {
    id: number;
    reference: string;
    title: string;
    status: string;
    updated_at: string;
    created_at: string;
};

type Props = {
    tickets: {
        data: TicketItem[];
    };
};

export default function PortalTicketsIndex({ tickets }: Props) {
    const [statusFilter, setStatusFilter] = useState('todos');

    const filteredTickets = useMemo(() => {
        return tickets.data.filter((ticket) => {
            if (statusFilter === 'todos') {
                return true;
            }

            return mapTicketStatusToPublicFilter(ticket.status) === statusFilter;
        });
    }, [tickets.data, statusFilter]);

    return (
        <PortalLayout
            title="Os meus pedidos"
            subtitle="Acompanhe os pedidos enviados a Junta."
            headerActions={
                <Link href={route('portal.tickets.create')} className="hidden rounded-2xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 lg:inline-flex">
                    Criar pedido
                </Link>
            }
        >
            <div className="space-y-4">
                <FilterPills
                    selected={statusFilter}
                    onChange={setStatusFilter}
                    options={[
                        { label: 'Todos', value: 'todos' },
                        { label: 'Em analise', value: 'em_analise' },
                        { label: 'Em tratamento', value: 'em_tratamento' },
                        { label: 'A aguardar informacao', value: 'aguarda_informacao' },
                        { label: 'Resolvidos / Encerrados', value: 'resolvido' },
                    ]}
                />

                <AppCard className="hidden overflow-hidden p-0 lg:block">
                    <table className="min-w-full text-sm">
                        <thead className="bg-slate-50 text-left text-slate-500">
                            <tr>
                                <th className="px-4 py-3">Referencia</th>
                                <th className="px-4 py-3">Assunto</th>
                                <th className="px-4 py-3">Estado</th>
                                <th className="px-4 py-3">Ultima atualizacao</th>
                                <th className="px-4 py-3">Criado em</th>
                                <th className="px-4 py-3">Acao</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100 bg-white">
                            {filteredTickets.map((ticket) => (
                                <tr key={ticket.id}>
                                    <td className="px-4 py-3 font-semibold text-blue-700">{ticket.reference}</td>
                                    <td className="px-4 py-3 text-slate-900">{ticket.title}</td>
                                    <td className="px-4 py-3">
                                        <PublicTicketStatusBadge status={ticket.status} />
                                    </td>
                                    <td className="px-4 py-3 text-slate-600">{new Date(ticket.updated_at).toLocaleString()}</td>
                                    <td className="px-4 py-3 text-slate-600">{new Date(ticket.created_at).toLocaleDateString()}</td>
                                    <td className="px-4 py-3">
                                        <Link href={route('portal.tickets.show', ticket.id)} className="font-semibold text-blue-700 hover:text-blue-900">
                                            Ver pedido
                                        </Link>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                    {filteredTickets.length === 0 ? (
                        <div className="p-6">
                            <EmptyState title="Ainda nao submeteu pedidos." description="Crie o seu primeiro pedido para comecar a acompanhar atualizacoes." />
                        </div>
                    ) : null}
                </AppCard>

                <div className="grid gap-3 lg:hidden">
                    {filteredTickets.map((ticket) => (
                        <AppCard key={ticket.id} className="p-4">
                            <Link href={route('portal.tickets.show', ticket.id)} className="flex items-start justify-between gap-2">
                                <div>
                                    <p className="text-sm font-bold text-blue-700">{ticket.reference}</p>
                                    <h3 className="mt-1 text-base font-semibold text-slate-900">{ticket.title}</h3>
                                </div>
                                <span className="text-slate-400">›</span>
                            </Link>
                            <div className="mt-4 space-y-2">
                                <PublicTicketStatusBadge status={ticket.status} />
                                <p className="text-xs text-slate-500">Ultima atualizacao: {new Date(ticket.updated_at).toLocaleString()}</p>
                                <p className="text-xs text-slate-500">Criado em: {new Date(ticket.created_at).toLocaleDateString()}</p>
                                <Link href={route('portal.tickets.show', ticket.id)} className="inline-flex text-sm font-semibold text-blue-700 hover:text-blue-900">
                                    Ver pedido
                                </Link>
                            </div>
                        </AppCard>
                    ))}

                    {filteredTickets.length === 0 ? <EmptyState title="Ainda nao submeteu pedidos." description="Crie o seu primeiro pedido para comecar a acompanhar atualizacoes." /> : null}
                </div>
            </div>

            <FloatingActionButton href={route('portal.tickets.create')} label="Criar pedido" />
        </PortalLayout>
    );
}
