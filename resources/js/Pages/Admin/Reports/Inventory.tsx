import SharedReportPage from '@/Pages/Admin/Reports/SharedReportPage';

type InventoryReportProps = {
    reportType: string;
    filters: Record<string, unknown>;
    summary: Record<string, unknown>;
    rows: { data: Record<string, unknown>[] };
    can: { export: boolean };
};

export default function InventoryReport(props: InventoryReportProps) {
    return (
        <SharedReportPage
            {...props}
            title="Relatório de Inventário"
            subtitle="Stock, empréstimos e reposição"
            routeName="admin.reports.inventory"
            columns={[
                { key: 'name', label: 'Item' },
                { key: 'category.name', label: 'Categoria' },
                { key: 'location.name', label: 'Localização' },
                { key: 'current_stock', label: 'Stock Atual' },
                { key: 'minimum_stock', label: 'Mínimo' },
                { key: 'status', label: 'Status' },
            ]}
        />
    );
}
