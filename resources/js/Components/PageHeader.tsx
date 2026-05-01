type PageHeaderProps = {
    title: string;
    description?: string;
    actions?: React.ReactNode;
};

export default function PageHeader({ title, description, actions }: PageHeaderProps) {
    return (
        <div className="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 className="text-2xl font-semibold text-slate-900">{title}</h2>
                {description ? <p className="mt-1 text-sm text-slate-600">{description}</p> : null}
            </div>
            {actions ? <div className="flex shrink-0 gap-3">{actions}</div> : null}
        </div>
    );
}
