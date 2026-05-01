type DateRangeFilterProps = {
    dateFrom?: string;
    dateTo?: string;
    onChange: (next: { date_from?: string; date_to?: string }) => void;
};

export default function DateRangeFilter({ dateFrom, dateTo, onChange }: DateRangeFilterProps) {
    return (
        <div className="grid gap-2 sm:grid-cols-2">
            <input
                type="date"
                value={dateFrom ?? ''}
                onChange={(event) => onChange({ date_from: event.target.value || undefined, date_to: dateTo })}
                className="rounded-xl border border-slate-300 px-3 py-2 text-sm"
            />
            <input
                type="date"
                value={dateTo ?? ''}
                onChange={(event) => onChange({ date_from: dateFrom, date_to: event.target.value || undefined })}
                className="rounded-xl border border-slate-300 px-3 py-2 text-sm"
            />
        </div>
    );
}
