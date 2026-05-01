type Props = { value: number };

export default function OperationalPlanProgressBar({ value }: Props) {
    const safeValue = Math.max(0, Math.min(100, Number(value || 0)));

    return (
        <div>
            <div className="h-2 w-full rounded-full bg-slate-200">
                <div className="h-2 rounded-full bg-emerald-600" style={{ width: `${safeValue}%` }} />
            </div>
            <p className="mt-1 text-xs text-slate-600">{safeValue}%</p>
        </div>
    );
}
