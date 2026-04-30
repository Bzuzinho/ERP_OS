import SpaceMaintenanceFilters from '@/Components/SpaceMaintenanceFilters';
import SpaceMaintenanceStatusBadge from '@/Components/SpaceMaintenanceStatusBadge';
import AdminLayout from '@/Layouts/AdminLayout';
import { Link } from '@inertiajs/react';

type RecordItem = { id: number; title: string; type: string; status: string; scheduled_at: string | null; space?: { id: number; name: string } | null; assignee?: { id: number; name: string } | null };
type Props = { records: { data: RecordItem[] }; statuses: string[]; types: string[] };

export default function AdminSpaceMaintenanceIndex({ records, statuses, types }: Props) {
    return (
        <AdminLayout title="Manutencao de Espacos" subtitle="Registos de manutencao" headerActions={<Link href={route('admin.space-maintenance.create')} className="inline-flex rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white">Nova Manutencao</Link>}>
            <SpaceMaintenanceFilters statuses={statuses} types={types} indexRouteName="admin.space-maintenance.index" />
            <div className="mt-4 space-y-2">
                {records.data.map((record) => (
                    <article key={record.id} className="rounded-2xl border border-slate-200 bg-white p-4 text-sm">
                        <Link href={route('admin.space-maintenance.show', record.id)} className="font-semibold text-slate-900">{record.title}</Link>
                        <p className="text-slate-700">{record.space?.name ?? '-'} • {record.type} • {record.assignee?.name ?? 'Sem responsavel'}</p>
                        <div className="mt-2"><SpaceMaintenanceStatusBadge status={record.status} /></div>
                    </article>
                ))}
            </div>
        </AdminLayout>
    );
}
