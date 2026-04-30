import SpaceCleaningStatusBadge from '@/Components/SpaceCleaningStatusBadge';
import AdminLayout from '@/Layouts/AdminLayout';
import { useForm } from '@inertiajs/react';

type RecordItem = { id: number; status: string; notes: string | null; scheduled_at: string | null; space?: { id: number; name: string } | null; reservation?: { id: number; purpose: string } | null; assignee?: { id: number; name: string } | null };
type Props = { record: RecordItem };

export default function AdminSpaceCleaningShow({ record }: Props) {
    const form = useForm({ notes: '' });
    return (
        <AdminLayout title="Detalhe de Limpeza" subtitle={`Registo #${record.id}`}>
            <section className="rounded-2xl border border-slate-200 bg-white p-4 text-sm text-slate-700">
                <p className="font-semibold text-slate-900">{record.space?.name ?? '-'}</p>
                <p>Reserva: {record.reservation?.purpose ?? '-'}</p>
                <p>Responsavel: {record.assignee?.name ?? 'Sem responsavel'}</p>
                <div className="mt-2"><SpaceCleaningStatusBadge status={record.status} /></div>
                <p className="mt-2">Agendada: {record.scheduled_at ? new Date(record.scheduled_at).toLocaleString() : '-'}</p>
                <p className="mt-1">{record.notes ?? '-'}</p>
            </section>
            <button onClick={() => form.post(route('admin.space-cleaning.complete', record.id))} className="mt-4 rounded-lg bg-emerald-700 px-3 py-2 text-xs font-medium text-white">Marcar como concluida</button>
        </AdminLayout>
    );
}
