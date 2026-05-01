import EmptyState from '@/Components/Reports/EmptyState';

type Column = {
    key: string;
    label: string;
};

type ReportTableProps = {
    columns: Column[];
    rows: Record<string, unknown>[];
};

export default function ReportTable({ columns, rows }: ReportTableProps) {
    const resolveValue = (row: Record<string, unknown>, key: string): unknown => {
        return key.split('.').reduce<unknown>((current, segment) => {
            if (current && typeof current === 'object' && segment in (current as Record<string, unknown>)) {
                return (current as Record<string, unknown>)[segment];
            }

            return undefined;
        }, row);
    };

    if (!rows.length) {
        return <EmptyState title="Sem registos para os filtros aplicados" />;
    }

    return (
        <div className="overflow-auto rounded-2xl border border-slate-200">
            <table className="min-w-full divide-y divide-slate-200 text-sm">
                <thead className="bg-slate-50">
                    <tr>
                        {columns.map((column) => (
                            <th key={column.key} className="px-3 py-2 text-left font-semibold text-slate-700">
                                {column.label}
                            </th>
                        ))}
                    </tr>
                </thead>
                <tbody className="divide-y divide-slate-100 bg-white">
                    {rows.map((row, index) => (
                        <tr key={index}>
                            {columns.map((column) => (
                                <td key={column.key} className="px-3 py-2 text-slate-700">
                                    {String(resolveValue(row, column.key) ?? '-')}
                                </td>
                            ))}
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}
