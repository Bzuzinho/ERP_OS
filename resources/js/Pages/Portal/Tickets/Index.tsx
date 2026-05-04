import AppBadge from '@/Components/App/AppBadge';
import AppCard from '@/Components/App/AppCard';
import EmptyState from '@/Components/App/EmptyState';
import FilterPills from '@/Components/App/FilterPills';
import FloatingActionButton from '@/Components/App/FloatingActionButton';
import SearchInput from '@/Components/App/SearchInput';
import PortalLayout from '@/Layouts/PortalLayout';
import { Link } from '@inertiajs/react';
import { useMemo, useState } from 'react';

type TicketItem = {
    id: number;
    reference: string;
    title: string;
    status: string;
    updated_at: string;
};

type Props = {
    tickets: {
        data: TicketItem[];
    };
};

export default function PortalTicketsIndex({ tickets }: Props) {
    const [search, setSearch] = useState('');
    const [statusFilter, setStatusFilter] = useState('all');

    const filteredTickets = useMemo(() => {
        return tickets.data.filter((ticket) => {
            const normalizedStatus = ticket.status.toLowerCase();
            const matchesSearch =
                ticket.reference.toLowerCase().includes(search.toLowerCase()) ||
                ticket.title.toLowerCase().includes(search.toLowerCase());

            if (!matchesSearch) return false;

            if (statusFilter === 'all') return true;
            if (statusFilter === 'open') return normalizedStatus.includes('abert') || normalizedStatus.includes('novo');
            if (statusFilter === 'urgent') return normalizedStatus.includes('urgent');
            if (statusFilter === 'closed') return normalizedStatus.includes('fech') || normalizedStatus.includes('resol');

            return true;
        });
    }, [tickets.data, search, statusFilter]);

    const statusTone = (status: string): 'blue' | 'amber' | 'green' | 'red' | 'slate' => {
        const normalized = status.toLowerCase();
        if (normalized.includes('fech') || normalized.includes('resol')) return 'green';
        if (normalized.includes('urgent')) return 'red';
        if (normalized.includes('pend') || normalized.includes('anal')) return 'amber';
        if (normalized.includes('abert') || normalized.includes('novo') || normalized.includes('exec')) return 'blue';
        return 'slate';
    };

    return (
        <PortalLayout
            title="Pedidos"
            subtitle="Gestão de pedidos e ocorrências"
            headerActions={
                <Link href={route('portal.tickets.create')} className="hidden rounded-2xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 lg:inline-flex">
                    Novo Pedido
                </Link>
            }
        >
            <div className="space-y-4">
                <div className="lg:hidden">
                    <SearchInput value={search} onChange={setSearch} placeholder="Pesquisar referência ou título" className="max-w-xl" />
                    <div className="mt-3">
                        <FilterPills
                            selected={statusFilter}
                            onChange={setStatusFilter}
                            options={[
                                { label: 'Todos', value: 'all' },
                                { label: 'Abertos', value: 'open' },
                                { label: 'Urgentes', value: 'urgent' },
                                { label: 'Fechados', value: 'closed' },
                            ]}
                        />
                    </div>
                </div>

                <AppCard className="hidden p-4 lg:block">
                    <div className="grid gap-3 md:grid-cols-3">
                        <SearchInput value={search} onChange={setSearch} placeholder="Pesquisar referência ou título" />
                        <select value={statusFilter} onChange={(event) => setStatusFilter(event.target.value)} className="rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700">
                            <option value="all">Todos os estados</option>
                            <option value="open">Abertos</option>
                            <option value="urgent">Urgentes</option>
                            <option value="closed">Fechados</option>
                        </select>
                        <div className="rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-600">
                            {filteredTickets.length} resultados
                        </div>
                    </div>
                </AppCard>

                <AppCard className="hidden overflow-hidden p-0 lg:block">
                    <table className="min-w-full text-sm">
                        <thead className="bg-slate-50 text-left text-slate-500">
                            <tr>
                                <th className="px-4 py-3">Referência</th>
                                <th className="px-4 py-3">Assunto</th>
                                <th className="px-4 py-3">Estado</th>
                                <th className="px-4 py-3">Atualizado</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100 bg-white">
                            {filteredTickets.map((ticket) => (
                                <tr key={ticket.id}>
                                    <td className="px-4 py-3 font-semibold text-blue-700">
                                        <Link href={route('portal.tickets.show', ticket.id)}>{ticket.reference}</Link>
                                    </td>
                                    <td className="px-4 py-3 text-slate-900">{ticket.title}</td>
                                    <td className="px-4 py-3"><AppBadge tone={statusTone(ticket.status)}>{ticket.status}</AppBadge></td>
                                    <td className="px-4 py-3 text-slate-600">{new Date(ticket.updated_at).toLocaleString()}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                    {filteredTickets.length === 0 ? <div className="p-6"><EmptyState title="Sem pedidos" description="Tente ajustar os filtros de pesquisa." /></div> : null}
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
                            <div className="mt-4 flex items-center justify-between gap-2">
                                <AppBadge tone={statusTone(ticket.status)}>{ticket.status}</AppBadge>
                                <p className="text-xs text-slate-500">{new Date(ticket.updated_at).toLocaleString()}</p>
                            </div>
                        </AppCard>
                    ))}

                    {filteredTickets.length === 0 ? <EmptyState title="Sem pedidos" description="Tente ajustar os filtros de pesquisa." /> : null}
                </div>
            </div>

            <FloatingActionButton href={route('portal.tickets.create')} label="Novo pedido" />
        </PortalLayout>
    );
}
