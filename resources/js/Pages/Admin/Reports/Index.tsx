import KpiCard from '@/Components/Reports/KpiCard';
import KpiGrid from '@/Components/Reports/KpiGrid';
import ReportSectionCard from '@/Components/Reports/ReportSectionCard';
import AdminLayout from '@/Layouts/AdminLayout';
import { Link } from '@inertiajs/react';

type Card = {
    key: string;
    title: string;
    description: string;
    route: string;
};

type ReportsIndexProps = {
    cards: Card[];
};

export default function ReportsIndex({ cards }: ReportsIndexProps) {
    return (
        <AdminLayout title="Relatórios" subtitle="Visão administrativa por módulo e exportação CSV">
            <div className="grid gap-4 lg:grid-cols-2 xl:grid-cols-4">
                {cards.map((card) => (
                    <ReportSectionCard key={card.key} title={card.title}>
                        <p className="text-sm text-slate-600">{card.description}</p>
                        <div className="mt-4">
                            <Link href={card.route} className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white">
                                Abrir relatório
                            </Link>
                        </div>
                    </ReportSectionCard>
                ))}
            </div>

            <div className="mt-4">
                <KpiGrid>
                    <KpiCard label="Módulos" value={cards.length} />
                    <KpiCard label="Exportação" value="CSV" />
                    <KpiCard label="Segurança" value="Permissions" />
                    <KpiCard label="Paginação" value="15 itens" />
                </KpiGrid>
            </div>
        </AdminLayout>
    );
}
