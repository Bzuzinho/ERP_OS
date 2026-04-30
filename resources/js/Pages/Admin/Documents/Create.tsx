import DocumentStatusBadge from '@/Components/DocumentStatusBadge';
import DocumentTypeBadge from '@/Components/DocumentTypeBadge';
import DocumentVisibilityBadge from '@/Components/DocumentVisibilityBadge';
import AdminLayout from '@/Layouts/AdminLayout';
import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type DocumentTypeItem = { id: number; name: string };

type Props = {
    documentTypes: DocumentTypeItem[];
    visibilities: string[];
    statuses: string[];
    relatedEntities: {
        tickets: { id: number; reference: string; title: string }[];
        contacts: { id: number; name: string }[];
        tasks: { id: number; title: string }[];
        events: { id: number; title: string }[];
        types: { label: string; value: string }[];
    };
};

export default function DocumentCreate({ documentTypes, visibilities, statuses, relatedEntities }: Props) {
    const form = useForm<{
        title: string;
        document_type_id: string;
        description: string;
        visibility: string;
        status: string;
        related_type: string;
        related_id: string;
        file: File | null;
    }>({
        title: '',
        document_type_id: '',
        description: '',
        visibility: 'internal',
        status: 'active',
        related_type: '',
        related_id: '',
        file: null,
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(route('admin.documents.store'), { forceFormData: true });
    };

    const relatedOptions =
        form.data.related_type === 'App\\Models\\Ticket' ? relatedEntities.tickets.map((t) => ({ id: t.id, label: `${t.reference} - ${t.title}` }))
            : form.data.related_type === 'App\\Models\\Contact' ? relatedEntities.contacts.map((c) => ({ id: c.id, label: c.name }))
                : form.data.related_type === 'App\\Models\\Task' ? relatedEntities.tasks.map((t) => ({ id: t.id, label: t.title }))
                    : form.data.related_type === 'App\\Models\\Event' ? relatedEntities.events.map((e) => ({ id: e.id, label: e.title }))
                        : [];

    return (
        <AdminLayout title="Novo Documento" subtitle="Criar um novo documento interno">
            <form onSubmit={submit} className="max-w-2xl space-y-5">
                <div className="rounded-2xl border border-slate-200 bg-white p-5 space-y-4">
                    <div>
                        <label className="text-sm font-medium text-slate-700">Título *</label>
                        <input value={form.data.title} onChange={(event) => form.setData('title', event.target.value)} className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                        {form.errors.title ? <p className="text-xs text-rose-600">{form.errors.title}</p> : null}
                    </div>
                    <div>
                        <label className="text-sm font-medium text-slate-700">Tipo</label>
                        <select value={form.data.document_type_id} onChange={(event) => form.setData('document_type_id', event.target.value)} className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                            <option value="">Sem tipo</option>
                            {documentTypes.map((type) => <option key={type.id} value={type.id}>{type.name}</option>)}
                        </select>
                    </div>
                    <div>
                        <label className="text-sm font-medium text-slate-700">Descrição</label>
                        <textarea value={form.data.description} onChange={(event) => form.setData('description', event.target.value)} className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm min-h-24" />
                    </div>
                    <div className="grid gap-4 md:grid-cols-2">
                        <div>
                            <label className="text-sm font-medium text-slate-700">Visibilidade</label>
                            <select value={form.data.visibility} onChange={(event) => form.setData('visibility', event.target.value)} className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                                {visibilities.map((v) => <option key={v} value={v}>{v}</option>)}
                            </select>
                        </div>
                        <div>
                            <label className="text-sm font-medium text-slate-700">Estado</label>
                            <select value={form.data.status} onChange={(event) => form.setData('status', event.target.value)} className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                                {statuses.map((s) => <option key={s} value={s}>{s}</option>)}
                            </select>
                        </div>
                    </div>
                    <div className="grid gap-4 md:grid-cols-2">
                        <div>
                            <label className="text-sm font-medium text-slate-700">Associar a</label>
                            <select value={form.data.related_type} onChange={(event) => form.setData('related_type', event.target.value)} className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                                <option value="">Nenhuma</option>
                                {relatedEntities.types.map((type) => <option key={type.value} value={type.value}>{type.label}</option>)}
                            </select>
                        </div>
                        <div>
                            <label className="text-sm font-medium text-slate-700">Entidade</label>
                            <select value={form.data.related_id} onChange={(event) => form.setData('related_id', event.target.value)} className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" disabled={!form.data.related_type}>
                                <option value="">Selecionar</option>
                                {relatedOptions.map((option) => <option key={option.id} value={option.id}>{option.label}</option>)}
                            </select>
                        </div>
                    </div>
                    <div>
                        <label className="text-sm font-medium text-slate-700">Ficheiro *</label>
                        <input type="file" onChange={(event) => form.setData('file', event.target.files?.[0] ?? null)} className="mt-1 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                        {form.errors.file ? <p className="text-xs text-rose-600">{form.errors.file}</p> : null}
                    </div>
                </div>

                <div className="flex gap-3">
                    <button type="submit" disabled={form.processing} className="rounded-xl bg-slate-900 px-5 py-2 text-sm font-medium text-white hover:bg-slate-700 disabled:opacity-50">
                        Criar Documento
                    </button>
                    <Link href={route('admin.documents.index')} className="rounded-xl border border-slate-300 px-5 py-2 text-sm text-slate-700 hover:bg-slate-100">
                        Cancelar
                    </Link>
                </div>
            </form>
        </AdminLayout>
    );
}
