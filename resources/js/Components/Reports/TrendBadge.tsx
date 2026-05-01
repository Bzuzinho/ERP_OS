type TrendBadgeProps = {
    value: number;
};

export default function TrendBadge({ value }: TrendBadgeProps) {
    const positive = value >= 0;

    return (
        <span
            className={
                positive
                    ? 'inline-flex rounded-full bg-emerald-100 px-2 py-1 text-xs font-medium text-emerald-800'
                    : 'inline-flex rounded-full bg-rose-100 px-2 py-1 text-xs font-medium text-rose-800'
            }
        >
            {positive ? '+' : ''}
            {value}%
        </span>
    );
}
