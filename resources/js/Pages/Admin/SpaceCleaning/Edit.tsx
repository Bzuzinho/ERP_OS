import AdminLayout from '@/Layouts/AdminLayout';
import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';

type RecordItem = { id: number; status: string; notes: string | null };
type Props = { record: RecordItem; statuses: string[] };

export default function AdminSpaceCleaningEdit({ record, statuses }: Props) {
    const { data, setData, put, processing } = useForm({ status: record.status, notes: record.notes ?? '' });
    const submit = (event: FormEvent<HTMLFormElement>) => { event.preventDefault(); put(route('admin.space-cleaning.update', record.id)); };
    return <AdminLayout title="Editar Limpeza" subtitle={`Registo #${record.id}`}><form onSubmit={submit} className="space-y-3 rounded-2xl border border-slate-200 bg-white p-4"><select value={data.status} onChange={(event) => setData('status', event.target.value)} className="w-full rounded-lg border-slate-300 text-sm">{statuses.map((status) => <option key={status} value={status}>{status}</option>)}</select><button disabled={processing} className="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white">Atualizar</button></form></AdminLayout>;
}
