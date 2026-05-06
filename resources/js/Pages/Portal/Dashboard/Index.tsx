import AppCard from '@/Components/App/AppCard';
import KpiCard from '@/Components/App/KpiCard';
import PortalLayout from '@/Layouts/PortalLayout';
import { Link } from '@inertiajs/react';

type PortalDashboardProps = {
    data: {
        kpis: Record<string, number>;
    };
};

type JourneyCard = {
    title: string;
    description: string;
    href: string;
};

const journeyCards: JourneyCard[] = [
    {
        title: 'Criar pedido',
        description: 'Reporte uma situacao ou faca um pedido a Junta.',
        href: route('portal.tickets.create'),
    },
    {
        title: 'Os meus pedidos',
        description: 'Acompanhe o estado dos pedidos que submeteu.',
        href: route('portal.tickets.index'),
    },
    {
        title: 'Agenda',
        description: 'Consulte eventos e marcacoes disponiveis.',
        href: route('portal.events.index'),
    },
    {
        title: 'Reservas',
        description: 'Peca ou acompanhe reservas de espacos.',
        href: route('portal.space-reservations.index'),
    },
    {
        title: 'Documentos',
        description: 'Consulte documentos disponiveis para si.',
        href: route('portal.documents.index'),
    },
    {
        title: 'Alertas',
        description: 'Veja notificacoes e respostas da Junta.',
        href: route('portal.notifications.index'),
    },
];

export default function PortalDashboard({ data }: PortalDashboardProps) {
    return (
        <PortalLayout title="Inicio" subtitle="Escolha a acao que pretende realizar.">
            <div className="grid grid-cols-2 gap-3 md:grid-cols-4 md:gap-4">
                <KpiCard label="Pedidos ativos" value={data.kpis.my_active_tickets ?? 0} />
                <KpiCard label="Pedidos resolvidos" value={data.kpis.resolved_tickets ?? 0} />
                <KpiCard label="Alertas nao lidos" value={data.kpis.unread_alerts ?? 0} />
                <KpiCard label="Proximos eventos e reservas" value={data.kpis.upcoming_items ?? 0} />
            </div>

            <div className="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                {journeyCards.map((card) => (
                    <AppCard key={card.title} className="flex h-full flex-col justify-between">
                        <div>
                            <h2 className="text-base font-bold text-slate-900">{card.title}</h2>
                            <p className="mt-2 text-sm text-slate-600">{card.description}</p>
                        </div>
                        <Link
                            href={card.href}
                            className="mt-5 inline-flex items-center justify-center rounded-2xl border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-100"
                        >
                            Abrir
                        </Link>
                    </AppCard>
                ))}
            </div>
        </PortalLayout>
    );
}
