import AdminLayout from '@/Layouts/AdminLayout';
import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';

type RecordItem = { id: number; title: string; type: string; status: string; description: string | null };
type Props = { record: RecordItem; statuses: string[]; types: string[] };

export default function AdminSpaceMaintenanceEdit({ record, statuses, types }: Props) {
    const { data, setData, put, processing } = useForm({ title: record.title, type: record.type, status: record.status, description: record.description ?? '' });
    const submit = (event: FormEvent<HTMLFormElement>) => { event.preventDefault(); put(route('admin.space-maintenance.update', record.id)); };
    return <AdminLayout title="Editar Manutencao" subtitle={record.title}><form onSubmit={submit} className="space-y-3 rounded-2xl border border-slate-200 bg-white p-4"><input value={data.title} onChange={(event) => setData('title', event.target.value)} className="w-full rounded-lg border-slate-300 text-sm" /><select value={data.status} onChange={(event) => setData('status', event.target.value)} className="w-full rounded-lg border-slate-300 text-sm">{statuses.map((status) => <option key={status} value={status}>{status}</option>)}</select><select value={data.type} onChange={(event) => setData('type', event.target.value)} className="w-full rounded-lg border-slate-300 text-sm">{types.map((type) => <option key={type} value={type}>{type}</option>)}</select><button disabled={processing} className="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white">Atualizar</button></form></AdminLayout>;
}
