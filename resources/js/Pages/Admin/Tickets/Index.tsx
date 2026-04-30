import TicketFilters from '@/Components/TicketFilters';
import TicketPriorityBadge from '@/Components/TicketPriorityBadge';
import TicketStatusBadge from '@/Components/TicketStatusBadge';
import AdminLayout from '@/Layouts/AdminLayout';
import { Link } from '@inertiajs/react';

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

export default function TicketsIndex({ tickets, filters, statuses, priorities, sources }: Props) {
    return (
        <AdminLayout
            title="Pedidos"
            subtitle="Gestao de pedidos e tickets municipais"
            headerActions={
                <Link href={route('admin.tickets.create')} className="inline-flex rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                    Criar Pedido
                </Link>
            }
        >
            <TicketFilters
                statuses={statuses}
                priorities={priorities}
                sources={sources}
                initialFilters={filters}
                indexRouteName="admin.tickets.index"
            />

            <div className="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white">
                <table className="min-w-full divide-y divide-slate-200 text-sm">
                    <thead className="bg-slate-50 text-left text-slate-600">
                        <tr>
                            <th className="px-4 py-3">Referencia</th>
                            <th className="px-4 py-3">Assunto</th>
                            <th className="px-4 py-3">Categoria</th>
                            <th className="px-4 py-3">Estado</th>
                            <th className="px-4 py-3">Prioridade</th>
                            <th className="px-4 py-3">Responsavel</th>
                            <th className="px-4 py-3">Prazo</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                        {tickets.data.map((ticket) => (
                            <tr key={ticket.id}>
                                <td className="px-4 py-3 font-medium text-slate-900">
                                    <Link href={route('admin.tickets.show', ticket.id)} className="hover:text-slate-700">
                                        {ticket.reference}
                                    </Link>
                                </td>
                                <td className="px-4 py-3 text-slate-700">{ticket.title}</td>
                                <td className="px-4 py-3 text-slate-700">{ticket.category ?? '-'}</td>
                                <td className="px-4 py-3"><TicketStatusBadge status={ticket.status} /></td>
                                <td className="px-4 py-3"><TicketPriorityBadge priority={ticket.priority} /></td>
                                <td className="px-4 py-3 text-slate-700">{ticket.assignee?.name ?? '-'}</td>
                                <td className="px-4 py-3 text-slate-700">{ticket.due_date ? new Date(ticket.due_date).toLocaleDateString() : '-'}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AdminLayout>
    );
}
