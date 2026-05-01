import SharedReportPage from '@/Pages/Admin/Reports/SharedReportPage';

type HrReportProps = {
    reportType: string;
    filters: Record<string, unknown>;
    summary: Record<string, unknown>;
    rows: { data: Record<string, unknown>[] };
    can: { export: boolean };
};

export default function HrReport(props: HrReportProps) {
    return (
        <SharedReportPage
            {...props}
            title="Relatório de Recursos Humanos"
            subtitle="Presenças, ausências e distribuição por departamento"
            routeName="admin.reports.hr"
            columns={[
                { key: 'employee.employee_number', label: 'Funcionário' },
                { key: 'status', label: 'Status hoje' },
                { key: 'check_in', label: 'Entrada' },
                { key: 'check_out', label: 'Saída' },
                { key: 'worked_minutes', label: 'Minutos' },
                { key: 'validated_at', label: 'Validação' },
                { key: 'date', label: 'Data' },
            ]}
        />
    );
}
