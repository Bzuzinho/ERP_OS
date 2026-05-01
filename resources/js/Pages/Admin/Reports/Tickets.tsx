import SharedReportPage from '@/Pages/Admin/Reports/SharedReportPage';

type TicketsReportProps = {
    reportType: string;
    filters: Record<string, unknown>;
    summary: Record<string, unknown>;
    rows: { data: Record<string, unknown>[] };
    can: { export: boolean };
};

export default function TicketsReport(props: TicketsReportProps) {
    return (
        <SharedReportPage
            {...props}
            title="Relatório de Pedidos"
            subtitle="KPIs e detalhe por estado, categoria e responsável"
            routeName="admin.reports.tickets"
            columns={[
                { key: 'reference', label: 'Referência' },
                { key: 'title', label: 'Título' },
                { key: 'category', label: 'Categoria' },
                { key: 'status', label: 'Estado' },
                { key: 'priority', label: 'Prioridade' },
                { key: 'source', label: 'Origem' },
                { key: 'due_date', label: 'Prazo' },
                { key: 'closed_at', label: 'Fechado em' },
            ]}
        />
    );
}
