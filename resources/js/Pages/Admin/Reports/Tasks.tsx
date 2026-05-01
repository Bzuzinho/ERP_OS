import SharedReportPage from '@/Pages/Admin/Reports/SharedReportPage';

type TasksReportProps = {
    reportType: string;
    filters: Record<string, unknown>;
    summary: Record<string, unknown>;
    rows: { data: Record<string, unknown>[] };
    can: { export: boolean };
};

export default function TasksReport(props: TasksReportProps) {
    return (
        <SharedReportPage
            {...props}
            title="Relatório de Tarefas"
            subtitle="KPIs de execução por estado, prioridade e responsável"
            routeName="admin.reports.tasks"
            columns={[
                { key: 'title', label: 'Título' },
                { key: 'ticket.reference', label: 'Ticket associado' },
                { key: 'status', label: 'Estado' },
                { key: 'priority', label: 'Prioridade' },
                { key: 'start_date', label: 'Início' },
                { key: 'due_date', label: 'Prazo' },
                { key: 'completed_at', label: 'Concluída em' },
            ]}
        />
    );
}
