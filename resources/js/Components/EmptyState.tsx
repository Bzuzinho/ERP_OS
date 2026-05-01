type EmptyStateProps = {
    title?: string;
    description?: string;
    action?: React.ReactNode;
};

export default function EmptyState({
    title = 'Sem resultados',
    description = 'Ainda não existem registos nesta área.',
    action,
}: EmptyStateProps) {
    return (
        <div className="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center">
            <div className="mb-4 text-4xl text-slate-300">○</div>
            <h3 className="text-base font-semibold text-slate-700">{title}</h3>
            <p className="mt-1 max-w-sm text-sm text-slate-500">{description}</p>
            {action ? <div className="mt-6">{action}</div> : null}
        </div>
    );
}
