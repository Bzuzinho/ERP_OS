import DocumentAccessRules from '@/Components/DocumentAccessRules';
import DocumentStatusBadge from '@/Components/DocumentStatusBadge';
import DocumentTypeBadge from '@/Components/DocumentTypeBadge';
import DocumentVersionList from '@/Components/DocumentVersionList';
import DocumentVisibilityBadge from '@/Components/DocumentVisibilityBadge';
import AdminLayout from '@/Layouts/AdminLayout';
import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type UserRef = { id: number; name: string };

type Document = {
    id: number;
    title: string;
    description?: string | null;
    visibility: string;
    status: string;
    current_version: number;
    file_name: string;
    original_name?: string | null;
    mime_type?: string | null;
    size?: number | null;
    related_type?: string | null;
    related_id?: number | null;
    created_at: string;
    type?: { id: number; name: string } | null;
    uploader?: UserRef | null;
    versions: Array<{ id: number; version: number; file_name: string; original_name?: string | null; created_at: string; uploader?: UserRef | null; notes?: string | null }>;
    access_rules: Array<{ id: number; user_id?: number | null; contact_id?: number | null; role_name?: string | null; permission: string; expires_at?: string | null; user?: UserRef | null; contact?: { id: number; name: string } | null }>;
    meeting_minute?: { id: number; title: string; event?: { id: number; title: string; start_at: string } | null } | null;
};

type Props = {
    document: Document;
    can: { download: boolean; manageAccess: boolean; update: boolean };
};

export default function DocumentShow({ document, can }: Props) {
    const versionForm = useForm({ file: null as File | null, notes: '' });

    const submitVersion = (event: FormEvent) => {
        event.preventDefault();
        versionForm.post(route('admin.documents.versions.store', document.id), { forceFormData: true });
    };

    return (
        <AdminLayout
            title={document.title}
            subtitle={`Versão ${document.current_version} - ${document.visibility}`}
            headerActions={
                <div className="flex gap-3">
                    {can.update ? (
                        <Link href={route('admin.documents.edit', document.id)} className="inline-flex rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                            Editar
                        </Link>
                    ) : null}
                    {can.download ? (
                        <a href={route('admin.documents.download', document.id)} className="inline-flex rounded-xl bg-emerald-700 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-600">
                            Download
                        </a>
                    ) : null}
                </div>
            }
        >
            <div className="grid gap-4 lg:grid-cols-3">
                <section className="rounded-2xl border border-slate-200 bg-white p-5 lg:col-span-2 space-y-4">
                    <div className="flex flex-wrap gap-2">
                        <DocumentStatusBadge status={document.status} />
                        <DocumentVisibilityBadge visibility={document.visibility} />
                        <DocumentTypeBadge typeName={document.type?.name} />
                    </div>
                    {document.description ? <p className="text-sm text-slate-700">{document.description}</p> : null}
                    <div className="grid gap-3 text-sm text-slate-600 md:grid-cols-2">
                        <p><span className="font-medium">Ficheiro:</span> {document.original_name ?? document.file_name}</p>
                        <p><span className="font-medium">MIME:</span> {document.mime_type ?? '-'}</p>
                        <p><span className="font-medium">Tamanho:</span> {document.size ? `${Math.round(document.size / 1024)} KB` : '-'}</p>
                        <p><span className="font-medium">Carregado por:</span> {document.uploader?.name ?? '-'}</p>
                        {document.related_type ? (
                            <p><span className="font-medium">Associado a:</span> {document.related_type.split('\\').pop()} #{document.related_id}</p>
                        ) : null}
                    </div>
                    {document.meeting_minute ? (
                        <div className="rounded-xl bg-slate-50 p-3 text-sm">
                            <p className="font-medium text-slate-900">Ata: {document.meeting_minute.title}</p>
                            {document.meeting_minute.event ? (
                                <p className="text-slate-600">Evento: {document.meeting_minute.event.title} • {new Date(document.meeting_minute.event.start_at).toLocaleString()}</p>
                            ) : null}
                        </div>
                    ) : null}
                </section>

                <aside className="space-y-4">
                    {can.update ? (
                        <form onSubmit={submitVersion} className="rounded-2xl border border-slate-200 bg-white p-4 space-y-3">
                            <h3 className="text-lg font-semibold text-slate-900">Nova versão</h3>
                            <input type="file" onChange={(event) => versionForm.setData('file', event.target.files?.[0] ?? null)} className="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                            <textarea placeholder="Notas" value={versionForm.data.notes} onChange={(event) => versionForm.setData('notes', event.target.value)} className="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm min-h-20" />
                            <button type="submit" className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Carregar versão</button>
                        </form>
                    ) : null}

                    {can.update ? (
                        <Link
                            href={route('admin.documents.destroy', document.id)}
                            method="delete"
                            as="button"
                            className="w-full rounded-xl border border-rose-300 bg-rose-50 px-4 py-2 text-sm font-medium text-rose-700 hover:bg-rose-100"
                        >
                            Arquivar documento
                        </Link>
                    ) : null}
                </aside>
            </div>

            <div className="mt-4">
                <DocumentVersionList versions={document.versions} />
            </div>

            <div className="mt-4">
                <DocumentAccessRules
                    documentId={document.id}
                    rules={document.access_rules}
                    canManage={can.manageAccess}
                />
            </div>
        </AdminLayout>
    );
}
