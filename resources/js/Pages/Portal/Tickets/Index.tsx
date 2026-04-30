import TicketStatusBadge from '@/Components/TicketStatusBadge';
import PortalLayout from '@/Layouts/PortalLayout';
import { Link } from '@inertiajs/react';

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
    return (
        <PortalLayout
            title="Os meus pedidos"
            subtitle="Acompanhe o estado dos pedidos submetidos"
            headerActions={
                <Link href={route('portal.tickets.create')} className="inline-flex rounded-xl bg-amber-600 px-4 py-2 text-sm font-medium text-white hover:bg-amber-500">
                    Novo Pedido
                </Link>
            }
        >
            <div className="space-y-3">
                {tickets.data.map((ticket) => (
                    <article key={ticket.id} className="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                        <div className="flex flex-wrap items-center justify-between gap-3">
                            <Link href={route('portal.tickets.show', ticket.id)} className="text-base font-semibold text-stone-900 hover:text-stone-700">
                                {ticket.reference} - {ticket.title}
                            </Link>
                            <TicketStatusBadge status={ticket.status} />
                        </div>
                        <p className="mt-2 text-xs text-stone-600">Ultima atualizacao: {new Date(ticket.updated_at).toLocaleString()}</p>
                    </article>
                ))}
            </div>
        </PortalLayout>
    );
}
