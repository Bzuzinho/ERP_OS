import AppBadge from '@/Components/App/AppBadge';
import AppCard from '@/Components/App/AppCard';
import EmptyState from '@/Components/App/EmptyState';
import FilterPills from '@/Components/App/FilterPills';
import FloatingActionButton from '@/Components/App/FloatingActionButton';
import SearchInput from '@/Components/App/SearchInput';
import AdminLayout from '@/Layouts/AdminLayout';
import { Link, router } from '@inertiajs/react';
import { useMemo, useState } from 'react';

type TicketItem = {
    id: number;
    reference: string;
    title: string;
    category: string | null;
    status: string;
    priority: string;
    source: string;
    due_date: string | null;
    assignee?: {
        id: number;
        name: string;
    } | null;
};

type Props = {
    tickets: {
        data: TicketItem[];
    };
    filters: {
        search?: string;
        status?: string;
        priority?: string;
        source?: string;
    };
    statuses: string[];
    priorities: string[];
    sources: string[];
};

const statusTone = (status: string): 'blue' | 'amber' | 'green' | 'red' | 'slate' => {
    const normalized = status.toLowerCase();
    if (normalized.includes('fech') || normalized.includes('resol')) return 'green';
    if (normalized.includes('urgent')) return 'red';
    if (normalized.includes('anal') || normalized.includes('pend')) return 'amber';
    if (normalized.includes('novo') || normalized.includes('abert') || normalized.includes('exec')) return 'blue';
    return 'slate';
};

const priorityTone = (priority: string): 'blue' | 'amber' | 'green' | 'red' | 'slate' => {
    const normalized = priority.toLowerCase();
    if (normalized.includes('alta') || normalized.includes('urgent')) return 'red';
    if (normalized.includes('media') || normalized.includes('média')) return 'amber';
    if (normalized.includes('baixa')) return 'green';
    return 'slate';
};

const findStatusValue = (statuses: string[], includesText: string) =>
    statuses.find((status) => status.toLowerCase().includes(includesText.toLowerCase())) ?? '';

export default function TicketsIndex({ tickets, filters, statuses, priorities, sources }: Props) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [selectedStatus, setSelectedStatus] = useState(filters.status ?? '');

    const statusOptions = useMemo(
        () => [
            { label: 'Todos', value: '' },
            { label: 'Abertos', value: findStatusValue(statuses, 'abert') },
            { label: 'Urgentes', value: findStatusValue(statuses, 'urgent') },
            { label: 'Fechados', value: findStatusValue(statuses, 'fech') },
        ],
        [statuses],
    );

    const applyFilters = (statusValue: string, searchValue: string) => {
        router.get(
            route('admin.tickets.index'),
            {
                search: searchValue || undefined,
                status: statusValue || undefined,
                priority: filters.priority || undefined,
                source: filters.source || undefined,
            },
            { preserveState: true, replace: true },
        );
    };

    const handleSearchBlur = () => applyFilters(selectedStatus, search);
    const handleStatusChange = (statusValue: string) => {
        setSelectedStatus(statusValue);
        applyFilters(statusValue, search);
    };

    return (
        <AdminLayout
            title="Pedidos"
            subtitle="Gestão de pedidos e ocorrências"
            headerActions={
                <Link href={route('admin.tickets.create')} className="hidden rounded-2xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 lg:inline-flex">
                    Criar Pedido
                </Link>
            }
        >
            <div className="space-y-4">
                <div className="lg:hidden">
                    <SearchInput
                        value={search}
                        onChange={setSearch}
                        placeholder="Pesquisar referência, assunto ou categoria"
                        className="max-w-xl"
                    />
                    <div className="mt-3 -mx-1" onMouseLeave={handleSearchBlur}>
                        <FilterPills options={statusOptions} selected={selectedStatus} onChange={handleStatusChange} />
                    </div>
                </div>

                <AppCard className="hidden p-4 lg:block">
                    <div className="grid gap-3 md:grid-cols-4">
                        <SearchInput
                            value={search}
                            onChange={setSearch}
                            placeholder="Pesquisar referência, assunto ou categoria"
                        />
                        <select
                            value={selectedStatus}
                            onChange={(event) => handleStatusChange(event.target.value)}
                            className="rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700"
                        >
                            <option value="">Todos os estados</option>
                            {statuses.map((status) => (
                                <option key={status} value={status}>{status}</option>
                            ))}
                        </select>
                        <select
                            value={filters.priority ?? ''}
                            onChange={(event) =>
                                router.get(
                                    route('admin.tickets.index'),
                                    {
                                        ...filters,
                                        priority: event.target.value || undefined,
                                        search: search || undefined,
                                        status: selectedStatus || undefined,
                                    },
                                    { preserveState: true, replace: true },
                                )
                            }
                            className="rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700"
                        >
                            <option value="">Todas as prioridades</option>
                            {priorities.map((priority) => (
                                <option key={priority} value={priority}>{priority}</option>
                            ))}
                        </select>
                        <select
                            value={filters.source ?? ''}
                            onChange={(event) =>
                                router.get(
                                    route('admin.tickets.index'),
                                    {
                                        ...filters,
                                        source: event.target.value || undefined,
                                        search: search || undefined,
                                        status: selectedStatus || undefined,
                                    },
                                    { preserveState: true, replace: true },
                                )
                            }
                            className="rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700"
                        >
                            <option value="">Todas as origens</option>
                            {sources.map((source) => (
                                <option key={source} value={source}>{source}</option>
                            ))}
                        </select>
                    </div>
                </AppCard>

                <AppCard className="hidden overflow-hidden p-0 lg:block">
                    <table className="min-w-full text-sm">
                        <thead className="bg-slate-50 text-left text-slate-500">
                            <tr>
                                <th className="px-4 py-3">Referência</th>
                                <th className="px-4 py-3">Assunto</th>
                                <th className="px-4 py-3">Categoria</th>
                                <th className="px-4 py-3">Estado</th>
                                <th className="px-4 py-3">Prioridade</th>
                                <th className="px-4 py-3">Responsável</th>
                                <th className="px-4 py-3">Prazo</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100 bg-white">
                            {tickets.data.map((ticket) => (
                                <tr key={ticket.id}>
                                    <td className="px-4 py-3 font-semibold text-blue-700">
                                        <Link href={route('admin.tickets.show', ticket.id)}>{ticket.reference}</Link>
                                    </td>
                                    <td className="px-4 py-3 text-slate-900">{ticket.title}</td>
                                    <td className="px-4 py-3 text-slate-600">{ticket.category ?? '-'}</td>
                                    <td className="px-4 py-3"><AppBadge tone={statusTone(ticket.status)}>{ticket.status}</AppBadge></td>
                                    <td className="px-4 py-3"><AppBadge tone={priorityTone(ticket.priority)}>{ticket.priority}</AppBadge></td>
                                    <td className="px-4 py-3 text-slate-600">{ticket.assignee?.name ?? '-'}</td>
                                    <td className="px-4 py-3 text-slate-600">{ticket.due_date ? new Date(ticket.due_date).toLocaleDateString() : '-'}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                    {tickets.data.length === 0 ? <div className="p-6"><EmptyState title="Sem pedidos" description="Não foram encontrados pedidos com os filtros atuais." /></div> : null}
                </AppCard>

                <div className="grid gap-3 lg:hidden">
                    {tickets.data.map((ticket) => (
                        <AppCard key={ticket.id} className="p-4">
                            <Link href={route('admin.tickets.show', ticket.id)} className="flex items-start justify-between gap-2">
                                <div>
                                    <p className="text-sm font-bold text-blue-700">{ticket.reference}</p>
                                    <h3 className="mt-1 text-base font-semibold text-slate-900">{ticket.title}</h3>
                                    <p className="mt-1 text-xs text-slate-500">{ticket.category ?? 'Sem categoria'}</p>
                                </div>
                                <span className="text-slate-400">›</span>
                            </Link>

                            <div className="mt-4 flex flex-wrap items-center gap-2">
                                <AppBadge tone={statusTone(ticket.status)}>{ticket.status}</AppBadge>
                                <AppBadge tone={priorityTone(ticket.priority)}>{ticket.priority}</AppBadge>
                                <span className="text-xs text-slate-500">
                                    {ticket.due_date ? new Date(ticket.due_date).toLocaleDateString() : 'Sem data'}
                                </span>
                            </div>
                        </AppCard>
                    ))}

                    {tickets.data.length === 0 ? (
                        <EmptyState title="Sem pedidos" description="Não foram encontrados pedidos com os filtros atuais." />
                    ) : null}
                </div>
            </div>

            <FloatingActionButton href={route('admin.tickets.create')} label="Novo pedido" />
        </AdminLayout>
    );
}
