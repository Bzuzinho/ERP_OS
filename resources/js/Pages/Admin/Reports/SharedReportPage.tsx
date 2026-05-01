import KpiCard from '@/Components/Reports/KpiCard';
import KpiGrid from '@/Components/Reports/KpiGrid';
import ReportExportButton from '@/Components/Reports/ReportExportButton';
import ReportFilters from '@/Components/Reports/ReportFilters';
import ReportTable from '@/Components/Reports/ReportTable';
import SimpleBarChart from '@/Components/Reports/SimpleBarChart';
import StatusBreakdown from '@/Components/Reports/StatusBreakdown';
import AdminLayout from '@/Layouts/AdminLayout';

type SharedReportPageProps = {
    title: string;
    subtitle: string;
    reportType: string;
    routeName: string;
    filters: Record<string, unknown>;
    summary: Record<string, unknown>;
    rows: {
        data: Record<string, unknown>[];
    };
    columns: { key: string; label: string }[];
    can: {
        export: boolean;
    };
};

export default function SharedReportPage({
    title,
    subtitle,
    reportType,
    routeName,
    filters,
    summary,
    rows,
    columns,
    can,
}: SharedReportPageProps) {
    const numbers = Object.entries(summary).filter(([, value]) => typeof value === 'number');
    const statusBreakdown = (summary.by_status ?? {}) as Record<string, number>;

    return (
        <AdminLayout
            title={title}
            subtitle={subtitle}
            headerActions={<ReportExportButton reportType={reportType} filters={filters} disabled={!can.export} />}
        >
            <ReportFilters routeName={routeName} filters={filters as Record<string, string | number | null | undefined>} />

            <div className="mt-4">
                <KpiGrid>
                    {numbers.slice(0, 8).map(([label, value]) => (
                        <KpiCard key={label} label={label} value={String(value)} />
                    ))}
                </KpiGrid>
            </div>

            <div className="mt-4 grid gap-4 xl:grid-cols-2">
                <SimpleBarChart
                    title="Top indicadores"
                    data={numbers.slice(0, 8).map(([label, value]) => ({ label, value: Number(value) }))}
                />
                <section className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <h3 className="text-sm font-semibold text-slate-900">Distribuição por estado</h3>
                    <div className="mt-3">
                        <StatusBreakdown values={statusBreakdown} />
                    </div>
                </section>
            </div>

            <div className="mt-4">
                <ReportTable columns={columns} rows={rows.data} />
            </div>
        </AdminLayout>
    );
}
