type Version = {
    id: number;
    version: number;
    file_name: string;
    original_name?: string | null;
    created_at: string;
    uploader?: { id: number; name: string } | null;
    notes?: string | null;
};

type Props = {
    versions: Version[];
};

export default function DocumentVersionList({ versions }: Props) {
    return (
        <section className="rounded-2xl border border-slate-200 bg-white p-4">
            <h3 className="text-lg font-semibold text-slate-900">Versões</h3>
            <ul className="mt-3 space-y-2 text-sm">
                {versions.map((version) => (
                    <li key={version.id} className="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <p className="font-medium text-slate-900">v{version.version} - {version.original_name ?? version.file_name}</p>
                        <p className="text-xs text-slate-600">{new Date(version.created_at).toLocaleString()} - {version.uploader?.name ?? 'Sistema'}</p>
                        {version.notes ? <p className="mt-1 text-xs text-slate-600">{version.notes}</p> : null}
                    </li>
                ))}
            </ul>
        </section>
    );
}
