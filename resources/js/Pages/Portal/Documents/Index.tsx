import DocumentStatusBadge from '@/Components/DocumentStatusBadge';
import DocumentVisibilityBadge from '@/Components/DocumentVisibilityBadge';
import PortalLayout from '@/Layouts/PortalLayout';
import { Link } from '@inertiajs/react';

type DocumentItem = {
    id: number;
    title: string;
    visibility: string;
    status: string;
    current_version: number;
    created_at: string;
    type?: { id: number; name: string } | null;
    can_download: boolean;
};

type Props = {
    documents: { data: DocumentItem[] };
    filters: { search?: string };
};

export default function PortalDocumentsIndex({ documents }: Props) {
    return (
        <PortalLayout title="Documentos">
            <div className="space-y-3">
                {documents.data.map((doc) => (
                    <div key={doc.id} className="flex items-center justify-between rounded-2xl border border-stone-200 bg-white p-4">
                        <div className="space-y-1">
                            <p className="font-medium text-stone-900">{doc.title}</p>
                            <div className="flex flex-wrap gap-2">
                                <DocumentVisibilityBadge visibility={doc.visibility} />
                                <DocumentStatusBadge status={doc.status} />
                                {doc.type ? <span className="inline-flex items-center rounded-full bg-stone-100 px-2 py-0.5 text-xs text-stone-600">{doc.type.name}</span> : null}
                            </div>
                            <p className="text-xs text-stone-500">v{doc.current_version} • {new Date(doc.created_at).toLocaleDateString()}</p>
                        </div>
                        <div className="flex gap-2">
                            <Link href={route('portal.documents.show', doc.id)} className="rounded-xl border border-stone-300 px-3 py-1.5 text-sm text-stone-700 hover:bg-stone-50">
                                Ver
                            </Link>
                            {doc.can_download ? (
                                <a href={route('portal.documents.download', doc.id)} className="rounded-xl bg-amber-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-amber-500">
                                    Download
                                </a>
                            ) : null}
                        </div>
                    </div>
                ))}
                {documents.data.length === 0 ? (
                    <p className="rounded-2xl border border-stone-200 bg-white p-6 text-center text-stone-500">Sem documentos disponíveis.</p>
                ) : null}
            </div>
        </PortalLayout>
    );
}
