import SharedReportPage from '@/Pages/Admin/Reports/SharedReportPage';

type EventsReportProps = {
    reportType: string;
    filters: Record<string, unknown>;
    summary: Record<string, unknown>;
    rows: { data: Record<string, unknown>[] };
    can: { export: boolean };
};

export default function EventsReport(props: EventsReportProps) {
    return (
        <SharedReportPage
            {...props}
            title="Relatório de Agenda/Eventos"
            subtitle="Eventos por tipo, estado e associação operacional"
            routeName="admin.reports.events"
            columns={[
                { key: 'title', label: 'Título' },
                { key: 'event_type', label: 'Tipo' },
                { key: 'status', label: 'Estado' },
                { key: 'start_at', label: 'Início' },
                { key: 'end_at', label: 'Fim' },
                { key: 'location_text', label: 'Local' },
            ]}
        />
    );
}
