import DocumentStatusBadge from '@/Components/DocumentStatusBadge';
import DocumentVisibilityBadge from '@/Components/DocumentVisibilityBadge';
import PortalLayout from '@/Layouts/PortalLayout';

type Document = {
    id: number;
    title: string;
    description?: string | null;
    visibility: string;
    status: string;
    current_version: number;
    original_name?: string | null;
    file_name: string;
    created_at: string;
    type?: { id: number; name: string } | null;
    uploader?: { id: number; name: string } | null;
};

type Props = {
    document: Document;
    can: { download: boolean };
};

export default function PortalDocumentShow({ document, can }: Props) {
    return (
        <PortalLayout title={document.title}>
            <div className="w-full max-w-2xl rounded-2xl border border-stone-200 bg-white p-4 space-y-4 sm:p-6">
                <div className="flex flex-wrap gap-2">
                    <DocumentVisibilityBadge visibility={document.visibility} />
                    <DocumentStatusBadge status={document.status} />
                    {document.type ? <span className="inline-flex items-center rounded-full bg-stone-100 px-2 py-0.5 text-xs text-stone-600">{document.type.name}</span> : null}
                </div>

                {document.description ? <p className="text-sm text-stone-700">{document.description}</p> : null}

                <dl className="grid gap-2 text-sm text-stone-600 md:grid-cols-2">
                    <div><dt className="font-medium">Ficheiro</dt><dd>{document.original_name ?? document.file_name}</dd></div>
                    <div><dt className="font-medium">Versão</dt><dd>v{document.current_version}</dd></div>
                    <div><dt className="font-medium">Carregado por</dt><dd>{document.uploader?.name ?? '-'}</dd></div>
                    <div><dt className="font-medium">Data</dt><dd>{new Date(document.created_at).toLocaleDateString()}</dd></div>
                </dl>

                {can.download ? (
                    <a href={route('portal.documents.download', document.id)} className="inline-flex rounded-xl bg-amber-600 px-5 py-2 text-sm font-medium text-white hover:bg-amber-500">
                        Download
                    </a>
                ) : null}
            </div>
        </PortalLayout>
    );
}
