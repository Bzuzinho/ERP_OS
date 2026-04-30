import DocumentFilters from '@/Components/DocumentFilters';
import DocumentStatusBadge from '@/Components/DocumentStatusBadge';
import DocumentTypeBadge from '@/Components/DocumentTypeBadge';
import DocumentVisibilityBadge from '@/Components/DocumentVisibilityBadge';
import AdminLayout from '@/Layouts/AdminLayout';
import { Link } from '@inertiajs/react';

type DocumentItem = {
    id: number;
    title: string;
    visibility: string;
    status: string;
    current_version: number;
    file_name: string;
    original_name?: string | null;
    related_type?: string | null;
    related_id?: number | null;
    created_at: string;
    type?: { id: number; name: string } | null;
    uploader?: { id: number; name: string } | null;
};

type Props = {
    documents: { data: DocumentItem[] };
    filters: {
        search?: string;
        documentTypeId?: string;
        visibility?: string;
        status?: string;
        relatedType?: string;
    };
    documentTypes: { id: number; name: string }[];
    visibilities: string[];
    statuses: string[];
    relatedTypes: string[];
};

export default function DocumentsIndex({ documents, filters, documentTypes, visibilities, statuses, relatedTypes }: Props) {
    return (
        <AdminLayout
            title="Documentos"
            subtitle="Gestão documental interna"
            headerActions={
                <Link href={route('admin.documents.create')} className="inline-flex rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                    Criar Documento
                </Link>
            }
        >
            <DocumentFilters
                documentTypes={documentTypes}
                visibilities={visibilities}
                statuses={statuses}
                relatedTypes={relatedTypes}
                initialFilters={filters}
            />

            <div className="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white">
                <table className="min-w-full divide-y divide-slate-200 text-sm">
                    <thead className="bg-slate-50 text-left text-slate-600">
                        <tr>
                            <th className="px-4 py-3">Título</th>
                            <th className="px-4 py-3">Tipo</th>
                            <th className="px-4 py-3">Visibilidade</th>
                            <th className="px-4 py-3">Estado</th>
                            <th className="px-4 py-3">Versão</th>
                            <th className="px-4 py-3">Carregado por</th>
                            <th className="px-4 py-3">Entidade</th>
                            <th className="px-4 py-3">Data</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                        {documents.data.map((doc) => (
                            <tr key={doc.id}>
                                <td className="px-4 py-3 font-medium text-slate-900">
                                    <Link href={route('admin.documents.show', doc.id)} className="hover:text-slate-700">
                                        {doc.title}
                                    </Link>
                                </td>
                                <td className="px-4 py-3"><DocumentTypeBadge typeName={doc.type?.name} /></td>
                                <td className="px-4 py-3"><DocumentVisibilityBadge visibility={doc.visibility} /></td>
                                <td className="px-4 py-3"><DocumentStatusBadge status={doc.status} /></td>
                                <td className="px-4 py-3 text-slate-700">v{doc.current_version}</td>
                                <td className="px-4 py-3 text-slate-700">{doc.uploader?.name ?? '-'}</td>
                                <td className="px-4 py-3 text-xs text-slate-500">
                                    {doc.related_type ? doc.related_type.split('\\').pop() : '-'} {doc.related_id ? `#${doc.related_id}` : ''}
                                </td>
                                <td className="px-4 py-3 text-slate-700">{new Date(doc.created_at).toLocaleDateString()}</td>
                            </tr>
                        ))}
                        {documents.data.length === 0 ? (
                            <tr><td colSpan={8} className="px-4 py-6 text-center text-slate-500">Sem documentos.</td></tr>
                        ) : null}
                    </tbody>
                </table>
            </div>
        </AdminLayout>
    );
}
