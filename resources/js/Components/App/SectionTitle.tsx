type SectionTitleProps = {
    title: string;
    subtitle?: string;
    className?: string;
};

export default function SectionTitle({ title, subtitle, className = '' }: SectionTitleProps) {
    return (
        <div className={className}>
            <h2 className="text-base font-bold text-slate-900 md:text-lg">{title}</h2>
            {subtitle ? <p className="mt-1 text-sm text-slate-500">{subtitle}</p> : null}
        </div>
    );
}
