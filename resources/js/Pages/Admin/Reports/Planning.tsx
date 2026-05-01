import SharedReportPage from '@/Pages/Admin/Reports/SharedReportPage';

type PlanningReportProps = {
    reportType: string;
    filters: Record<string, unknown>;
    summary: Record<string, unknown>;
    rows: { data: Record<string, unknown>[] };
    can: { export: boolean };
};

export default function PlanningReport(props: PlanningReportProps) {
    return (
        <SharedReportPage
            {...props}
            title="Relatório de Planeamento"
            subtitle="Planos, progresso e recorrências operacionais"
            routeName="admin.reports.planning"
            columns={[
                { key: 'title', label: 'Plano' },
                { key: 'plan_type', label: 'Tipo' },
                { key: 'status', label: 'Estado' },
                { key: 'visibility', label: 'Visibilidade' },
                { key: 'start_date', label: 'Início' },
                { key: 'end_date', label: 'Fim' },
                { key: 'progress_percent', label: 'Progresso' },
            ]}
        />
    );
}
