import AdminLayout from '@/Layouts/AdminLayout';
import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';

type Props = { statuses: string[]; types: string[]; spaces: { id: number; name: string }[]; users: { id: number; name: string }[] };

export default function AdminSpaceMaintenanceCreate({ statuses, types, spaces, users }: Props) {
    const { data, setData, post, processing } = useForm({ space_id: spaces[0]?.id ?? 0, type: types[0] ?? 'maintenance', status: statuses[0] ?? 'pending', title: '', description: '', assigned_to: users[0]?.id ?? 0, scheduled_at: '' });
    const submit = (event: FormEvent<HTMLFormElement>) => { event.preventDefault(); post(route('admin.space-maintenance.store')); };
    return <AdminLayout title="Criar Manutencao" subtitle="Novo registo"><form onSubmit={submit} className="space-y-3 rounded-2xl border border-slate-200 bg-white p-4"><input value={data.title} onChange={(event) => setData('title', event.target.value)} placeholder="Titulo" className="w-full rounded-lg border-slate-300 text-sm" /><button disabled={processing} className="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white">Guardar</button></form></AdminLayout>;
}
