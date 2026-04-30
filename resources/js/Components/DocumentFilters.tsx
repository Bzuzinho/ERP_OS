import { router } from '@inertiajs/react';
import { FormEvent, useState } from 'react';

type Option = { id: number; name: string };

type Props = {
    documentTypes: Option[];
    visibilities: string[];
    statuses: string[];
    relatedTypes: string[];
    initialFilters: {
        search?: string;
        documentTypeId?: string;
        visibility?: string;
        status?: string;
        relatedType?: string;
    };
};

export default function DocumentFilters({ documentTypes, visibilities, statuses, relatedTypes, initialFilters }: Props) {
    const [search, setSearch] = useState(initialFilters.search ?? '');
    const [documentTypeId, setDocumentTypeId] = useState(initialFilters.documentTypeId ?? '');
    const [visibility, setVisibility] = useState(initialFilters.visibility ?? '');
    const [status, setStatus] = useState(initialFilters.status ?? '');
    const [relatedType, setRelatedType] = useState(initialFilters.relatedType ?? '');

    const submit = (event: FormEvent) => {
        event.preventDefault();

        router.get(route('admin.documents.index'), {
            search: search || undefined,
            document_type_id: documentTypeId || undefined,
            visibility: visibility || undefined,
            status: status || undefined,
            related_type: relatedType || undefined,
        }, { preserveState: true, replace: true });
    };

    return (
        <form onSubmit={submit} className="grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 md:grid-cols-6">
            <input value={search} onChange={(event) => setSearch(event.target.value)} placeholder="Pesquisar documento" className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
            <select value={documentTypeId} onChange={(event) => setDocumentTypeId(event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                <option value="">Tipo</option>
                {documentTypes.map((type) => <option key={type.id} value={type.id}>{type.name}</option>)}
            </select>
            <select value={visibility} onChange={(event) => setVisibility(event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                <option value="">Visibilidade</option>
                {visibilities.map((item) => <option key={item} value={item}>{item}</option>)}
            </select>
            <select value={status} onChange={(event) => setStatus(event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                <option value="">Estado</option>
                {statuses.map((item) => <option key={item} value={item}>{item}</option>)}
            </select>
            <select value={relatedType} onChange={(event) => setRelatedType(event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                <option value="">Entidade</option>
                {relatedTypes.map((item) => <option key={item} value={item}>{item}</option>)}
            </select>
            <button type="submit" className="rounded-xl bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700">Filtrar</button>
        </form>
    );
}
