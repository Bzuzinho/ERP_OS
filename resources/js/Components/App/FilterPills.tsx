type FilterOption = {
    label: string;
    value: string;
};

type FilterPillsProps = {
    options: FilterOption[];
    selected: string;
    onChange: (value: string) => void;
};

export default function FilterPills({ options, selected, onChange }: FilterPillsProps) {
    return (
        <div className="flex gap-2 overflow-x-auto pb-1">
            {options.map((option) => {
                const isActive = option.value === selected;

                return (
                    <button
                        type="button"
                        key={option.value}
                        onClick={() => onChange(option.value)}
                        className={
                            isActive
                                ? 'rounded-full bg-blue-600 px-3.5 py-2 text-sm font-semibold text-white'
                                : 'rounded-full bg-slate-100 px-3.5 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-200'
                        }
                    >
                        {option.label}
                    </button>
                );
            })}
        </div>
    );
}
