import AdminLayout from '@/Layouts/AdminLayout';
import { Link } from '@inertiajs/react';

type ServiceAreaRow = {
    id: number;
    name: string;
    slug: string;
    is_active: boolean;
    users_count: number;
    tickets_count: number;
};

type Props = {
    serviceAreas: {
        data: ServiceAreaRow[];
    };
};

export default function ServiceAreasIndex({ serviceAreas }: Props) {
    return (
        <AdminLayout
            title="Areas funcionais"
            subtitle="Responsabilidades e equipas transversais"
            headerActions={<Link href={route('admin.service-areas.create')} className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white">Nova area</Link>}
        >
            <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                <table className="min-w-full divide-y divide-slate-200 text-sm">
                    <thead className="bg-slate-50 text-left text-slate-600">
                        <tr>
                            <th className="px-4 py-3">Nome</th>
                            <th className="px-4 py-3">Slug</th>
                            <th className="px-4 py-3">Utilizadores</th>
                            <th className="px-4 py-3">Pedidos</th>
                            <th className="px-4 py-3">Estado</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                        {serviceAreas.data.map((area) => (
                            <tr key={area.id}>
                                <td className="px-4 py-3">
                                    <Link href={route('admin.service-areas.show', area.id)} className="font-semibold text-slate-900 hover:text-blue-700">
                                        {area.name}
                                    </Link>
                                </td>
                                <td className="px-4 py-3 text-slate-600">{area.slug}</td>
                                <td className="px-4 py-3">{area.users_count}</td>
                                <td className="px-4 py-3">{area.tickets_count}</td>
                                <td className="px-4 py-3">{area.is_active ? 'Ativa' : 'Inativa'}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AdminLayout>
    );
}
