import SharedReportPage from '@/Pages/Admin/Reports/SharedReportPage';

type SpacesReportProps = {
    reportType: string;
    filters: Record<string, unknown>;
    summary: Record<string, unknown>;
    rows: { data: Record<string, unknown>[] };
    can: { export: boolean };
};

export default function SpacesReport(props: SpacesReportProps) {
    return (
        <SharedReportPage
            {...props}
            title="Relatório de Espaços/Reservas"
            subtitle="Ocupação e estado das reservas e aprovações"
            routeName="admin.reports.spaces"
            columns={[
                { key: 'space.name', label: 'Espaço' },
                { key: 'id', label: 'Reserva' },
                { key: 'purpose', label: 'Finalidade' },
                { key: 'status', label: 'Estado' },
                { key: 'start_at', label: 'Início' },
                { key: 'end_at', label: 'Fim' },
            ]}
        />
    );
}
