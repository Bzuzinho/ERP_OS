type ReportExportButtonProps = {
    reportType: string;
    filters: Record<string, unknown>;
    disabled?: boolean;
};

export default function ReportExportButton({ reportType, filters, disabled }: ReportExportButtonProps) {
    const onExport = () => {
        const params = new URLSearchParams({
            report_type: reportType,
            format: 'csv',
        });

        Object.entries(filters).forEach(([key, value]) => {
            if (value !== null && value !== undefined && String(value).length > 0) {
                params.set(key, String(value));
            }
        });

        window.location.href = `${route('admin.reports.export')}?${params.toString()}`;
    };

    return (
        <button
            type="button"
            onClick={onExport}
            disabled={disabled}
            className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700 disabled:cursor-not-allowed disabled:bg-slate-400"
        >
            Exportar CSV
        </button>
    );
}
