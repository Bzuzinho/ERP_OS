import PortalLayout from '@/Layouts/PortalLayout';

type Document = {
    id: number;
    title: string;
    description?: string | null;
    current_version: number;
    original_name?: string | null;
    file_name: string;
    created_at: string;
    type?: { id: number; name: string } | null;
};

type Props = {
    document: Document;
    can: { download: boolean };
};

export default function PortalDocumentShow({ document, can }: Props) {
    return (
        <PortalLayout title="Documentos" subtitle="Documentos disponiveis para consulta.">
            <div className="w-full max-w-2xl space-y-4 rounded-2xl border border-stone-200 bg-white p-4 sm:p-6">
                <h1 className="text-xl font-semibold text-slate-900">{document.title}</h1>
                {document.description ? <p className="text-sm text-stone-700">{document.description}</p> : null}

                <dl className="grid gap-2 text-sm text-stone-600 md:grid-cols-2">
                    <div>
                        <dt className="font-medium">Tipo</dt>
                        <dd>{document.type?.name ?? 'Documento'}</dd>
                    </div>
                    <div>
                        <dt className="font-medium">Data</dt>
                        <dd>{new Date(document.created_at).toLocaleDateString()}</dd>
                    </div>
                    <div>
                        <dt className="font-medium">Ficheiro</dt>
                        <dd>{document.original_name ?? document.file_name}</dd>
                    </div>
                    <div>
                        <dt className="font-medium">Versao</dt>
                        <dd>v{document.current_version}</dd>
                    </div>
                </dl>

                {can.download ? (
                    <a href={route('portal.documents.download', document.id)} className="inline-flex rounded-xl bg-amber-600 px-5 py-2 text-sm font-medium text-white hover:bg-amber-500">
                        Descarregar
                    </a>
                ) : null}
            </div>
        </PortalLayout>
    );
}
