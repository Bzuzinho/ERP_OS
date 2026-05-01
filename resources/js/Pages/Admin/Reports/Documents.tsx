import SharedReportPage from '@/Pages/Admin/Reports/SharedReportPage';

type DocumentsReportProps = {
    reportType: string;
    filters: Record<string, unknown>;
    summary: Record<string, unknown>;
    rows: { data: Record<string, unknown>[] };
    can: { export: boolean };
};

export default function DocumentsReport(props: DocumentsReportProps) {
    return (
        <SharedReportPage
            {...props}
            title="Relatório de Documentos"
            subtitle="Documentação ativa, visibilidade e atas"
            routeName="admin.reports.documents"
            columns={[
                { key: 'title', label: 'Título' },
                { key: 'type.name', label: 'Tipo' },
                { key: 'visibility', label: 'Visibilidade' },
                { key: 'status', label: 'Estado' },
                { key: 'current_version', label: 'Versão' },
                { key: 'created_at', label: 'Criado em' },
            ]}
        />
    );
}
