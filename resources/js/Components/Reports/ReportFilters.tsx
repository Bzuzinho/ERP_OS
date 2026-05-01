import DateRangeFilter from '@/Components/Reports/DateRangeFilter';
import { router } from '@inertiajs/react';

type ReportFiltersProps = {
    routeName: string;
    filters: Record<string, string | number | null | undefined>;
};

export default function ReportFilters({ routeName, filters }: ReportFiltersProps) {
    const submit = (next: Record<string, string | number | null | undefined>) => {
        router.get(route(routeName), next, { preserveState: true, preserveScroll: true });
    };

    return (
        <section className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div className="grid gap-3 lg:grid-cols-4">
                <DateRangeFilter
                    dateFrom={String(filters.date_from ?? '')}
                    dateTo={String(filters.date_to ?? '')}
                    onChange={(next) => submit({ ...filters, ...next })}
                />
                <input
                    placeholder="Estado"
                    defaultValue={String(filters.status ?? '')}
                    onBlur={(event) => submit({ ...filters, status: event.target.value || undefined })}
                    className="rounded-xl border border-slate-300 px-3 py-2 text-sm"
                />
                <input
                    placeholder="Categoria"
                    defaultValue={String(filters.category ?? '')}
                    onBlur={(event) => submit({ ...filters, category: event.target.value || undefined })}
                    className="rounded-xl border border-slate-300 px-3 py-2 text-sm"
                />
                <input
                    placeholder="Pesquisar"
                    defaultValue={String(filters.search ?? '')}
                    onBlur={(event) => submit({ ...filters, search: event.target.value || undefined })}
                    className="rounded-xl border border-slate-300 px-3 py-2 text-sm"
                />
            </div>
        </section>
    );
}
