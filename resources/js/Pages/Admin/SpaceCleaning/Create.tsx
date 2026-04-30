import AdminLayout from '@/Layouts/AdminLayout';
import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';

type Props = { statuses: string[]; spaces: { id: number; name: string }[] };

export default function AdminSpaceCleaningCreate({ statuses, spaces }: Props) {
    const { data, setData, post, processing } = useForm({ space_id: spaces[0]?.id ?? 0, status: statuses[0] ?? 'pending', scheduled_at: '' });
    const submit = (event: FormEvent<HTMLFormElement>) => { event.preventDefault(); post(route('admin.space-cleaning.store')); };
    return <AdminLayout title="Criar Limpeza" subtitle="Novo registo"><form onSubmit={submit} className="space-y-3 rounded-2xl border border-slate-200 bg-white p-4"><select value={data.space_id} onChange={(event) => setData('space_id', Number(event.target.value))} className="w-full rounded-lg border-slate-300 text-sm">{spaces.map((space) => <option key={space.id} value={space.id}>{space.name}</option>)}</select><button disabled={processing} className="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white">Guardar</button></form></AdminLayout>;
}
