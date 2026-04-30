import SpaceMaintenanceStatusBadge from '@/Components/SpaceMaintenanceStatusBadge';
import AdminLayout from '@/Layouts/AdminLayout';

type RecordItem = { id: number; title: string; type: string; status: string; description: string | null; notes: string | null; space?: { id: number; name: string } | null; assignee?: { id: number; name: string } | null };
type Props = { record: RecordItem };

export default function AdminSpaceMaintenanceShow({ record }: Props) {
    return (
        <AdminLayout title="Detalhe de Manutencao" subtitle={record.title}>
            <section className="rounded-2xl border border-slate-200 bg-white p-4 text-sm text-slate-700">
                <p className="font-semibold text-slate-900">{record.title}</p>
                <p>{record.space?.name ?? '-'} • {record.type} • {record.assignee?.name ?? 'Sem responsavel'}</p>
                <div className="mt-2"><SpaceMaintenanceStatusBadge status={record.status} /></div>
                <p className="mt-2">{record.description ?? '-'}</p>
                <p className="mt-1">{record.notes ?? '-'}</p>
            </section>
        </AdminLayout>
    );
}
