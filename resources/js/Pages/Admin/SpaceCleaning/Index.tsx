import SpaceCleaningFilters from '@/Components/SpaceCleaningFilters';
import SpaceCleaningStatusBadge from '@/Components/SpaceCleaningStatusBadge';
import AdminLayout from '@/Layouts/AdminLayout';
import { Link } from '@inertiajs/react';

type RecordItem = { id: number; status: string; scheduled_at: string | null; space?: { id: number; name: string } | null; reservation?: { id: number; purpose: string } | null; assignee?: { id: number; name: string } | null };
type Props = { records: { data: RecordItem[] }; statuses: string[] };

export default function AdminSpaceCleaningIndex({ records, statuses }: Props) {
    return (
        <AdminLayout title="Limpeza de Espacos" subtitle="Registos de limpeza" headerActions={<Link href={route('admin.space-cleaning.create')} className="inline-flex rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white">Nova Limpeza</Link>}>
            <SpaceCleaningFilters statuses={statuses} indexRouteName="admin.space-cleaning.index" />
            <div className="mt-4 space-y-2">
                {records.data.map((record) => (
                    <article key={record.id} className="rounded-2xl border border-slate-200 bg-white p-4 text-sm">
                        <Link href={route('admin.space-cleaning.show', record.id)} className="font-semibold text-slate-900">{record.space?.name ?? '-'}</Link>
                        <p className="text-slate-700">{record.reservation?.purpose ?? 'Sem reserva'} • {record.assignee?.name ?? 'Sem responsavel'}</p>
                        <div className="mt-2"><SpaceCleaningStatusBadge status={record.status} /></div>
                    </article>
                ))}
            </div>
        </AdminLayout>
    );
}
