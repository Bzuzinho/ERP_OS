import SpaceFilters from '@/Components/SpaceFilters';
import SpaceStatusBadge from '@/Components/SpaceStatusBadge';
import AdminLayout from '@/Layouts/AdminLayout';
import { Link } from '@inertiajs/react';

type SpaceItem = {
    id: number;
    name: string;
    location_text: string | null;
    capacity: number | null;
    status: string;
    is_public: boolean;
    requires_approval: boolean;
    has_cleaning_required: boolean;
    is_active: boolean;
};

type Props = {
    spaces: { data: SpaceItem[] };
    statuses: string[];
    filters?: { search?: string; status?: string; isPublic?: string; isActive?: string };
};

export default function AdminSpacesIndex({ spaces, statuses, filters }: Props) {
    return (
        <AdminLayout title="Espacos" subtitle="Gestao de espacos da junta" headerActions={<Link href={route('admin.spaces.create')} className="inline-flex rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white">Criar Espaco</Link>}>
            <SpaceFilters statuses={statuses} indexRouteName="admin.spaces.index" initialFilters={filters} />
            <div className="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white">
                <table className="min-w-full divide-y divide-slate-200 text-sm">
                    <thead className="bg-slate-50 text-left text-slate-600"><tr><th className="px-4 py-3">Nome</th><th className="px-4 py-3">Localizacao</th><th className="px-4 py-3">Capacidade</th><th className="px-4 py-3">Estado</th><th className="px-4 py-3">Publico</th><th className="px-4 py-3">Aprovacao</th><th className="px-4 py-3">Limpeza</th><th className="px-4 py-3">Ativo</th></tr></thead>
                    <tbody className="divide-y divide-slate-100">
                        {spaces.data.map((space) => (
                            <tr key={space.id}>
                                <td className="px-4 py-3 font-medium text-slate-900"><Link href={route('admin.spaces.show', space.id)}>{space.name}</Link></td>
                                <td className="px-4 py-3">{space.location_text ?? '-'}</td>
                                <td className="px-4 py-3">{space.capacity ?? '-'}</td>
                                <td className="px-4 py-3"><SpaceStatusBadge status={space.status} /></td>
                                <td className="px-4 py-3">{space.is_public ? 'Sim' : 'Nao'}</td>
                                <td className="px-4 py-3">{space.requires_approval ? 'Sim' : 'Nao'}</td>
                                <td className="px-4 py-3">{space.has_cleaning_required ? 'Sim' : 'Nao'}</td>
                                <td className="px-4 py-3">{space.is_active ? 'Sim' : 'Nao'}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AdminLayout>
    );
}
