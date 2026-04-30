import AdminLayout from '@/Layouts/AdminLayout';
import { useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type DocumentType = { id: number; name: string };
type Document = {
    id: number;
    title: string;
    description?: string | null;
    visibility: string;
    status: string;
    related_type?: string | null;
    related_id?: number | null;
    document_type_id?: number | null;
};

type RelatedEntity = { id: number; label: string };

type Props = {
    document: Document;
    documentTypes: DocumentType[];
    visibilities: string[];
    statuses: string[];
    relatedEntities: {
        tickets: RelatedEntity[];
        contacts: RelatedEntity[];
        tasks: RelatedEntity[];
        events: RelatedEntity[];
        types: { label: string; value: string }[];
    };
};

export default function DocumentEdit({ document, documentTypes, visibilities, statuses, relatedEntities }: Props) {
    const form = useForm({
        title: document.title,
        description: document.description ?? '',
        document_type_id: document.document_type_id?.toString() ?? '',
        visibility: document.visibility,
        status: document.status,
        related_type: document.related_type ?? '',
        related_id: document.related_id?.toString() ?? '',
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.put(route('admin.documents.update', document.id));
    };

    const relatedOptions =
        form.data.related_type === 'App\\Models\\Ticket' ? relatedEntities.tickets
            : form.data.related_type === 'App\\Models\\Contact' ? relatedEntities.contacts
                : form.data.related_type === 'App\\Models\\Task' ? relatedEntities.tasks
                    : form.data.related_type === 'App\\Models\\Event' ? relatedEntities.events
                        : [];

    return (
        <AdminLayout title="Editar Documento" subtitle={document.title}>
            <form onSubmit={submit} className="max-w-2xl space-y-6 rounded-2xl border border-slate-200 bg-white p-6">
                <div>
                    <label className="block text-sm font-medium text-slate-700">Título *</label>
                    <input type="text" value={form.data.title} onChange={(event) => form.setData('title', event.target.value)} required className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400" />
                    {form.errors.title ? <p className="mt-1 text-xs text-rose-600">{form.errors.title}</p> : null}
                </div>

                <div>
                    <label className="block text-sm font-medium text-slate-700">Tipo de documento</label>
                    <select value={form.data.document_type_id} onChange={(event) => form.setData('document_type_id', event.target.value)} className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        <option value="">-- Sem tipo --</option>
                        {documentTypes.map((dt) => <option key={dt.id} value={dt.id}>{dt.name}</option>)}
                    </select>
                </div>

                <div>
                    <label className="block text-sm font-medium text-slate-700">Descrição</label>
                    <textarea value={form.data.description} onChange={(event) => form.setData('description', event.target.value)} rows={4} className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                </div>

                <div className="grid gap-4 md:grid-cols-2">
                    <div>
                        <label className="block text-sm font-medium text-slate-700">Visibilidade *</label>
                        <select value={form.data.visibility} onChange={(event) => form.setData('visibility', event.target.value)} className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                            {visibilities.map((v) => <option key={v} value={v}>{v}</option>)}
                        </select>
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-slate-700">Estado *</label>
                        <select value={form.data.status} onChange={(event) => form.setData('status', event.target.value)} className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                            {statuses.map((s) => <option key={s} value={s}>{s}</option>)}
                        </select>
                    </div>
                </div>

                <div>
                    <label className="block text-sm font-medium text-slate-700">Entidade relacionada</label>
                    <select value={form.data.related_type} onChange={(event) => { form.setData('related_type', event.target.value); form.setData('related_id', ''); }} className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        <option value="">-- Nenhuma --</option>
                        {relatedEntities.types.map((t) => <option key={t.value} value={t.value}>{t.label}</option>)}
                    </select>
                </div>
                {form.data.related_type && relatedOptions.length > 0 ? (
                    <div>
                        <label className="block text-sm font-medium text-slate-700">Selecionar</label>
                        <select value={form.data.related_id} onChange={(event) => form.setData('related_id', event.target.value)} className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                            <option value="">-- Selecionar --</option>
                            {relatedOptions.map((o) => <option key={o.id} value={o.id}>{o.label}</option>)}
                        </select>
                    </div>
                ) : null}

                <div className="flex gap-3">
                    <button type="submit" disabled={form.processing} className="rounded-xl bg-slate-900 px-5 py-2 text-sm font-medium text-white hover:bg-slate-700 disabled:opacity-50">
                        Guardar alterações
                    </button>
                    <a href={route('admin.documents.show', document.id)} className="rounded-xl border border-slate-300 px-5 py-2 text-sm text-slate-700 hover:bg-slate-50">
                        Cancelar
                    </a>
                </div>
            </form>
        </AdminLayout>
    );
}
