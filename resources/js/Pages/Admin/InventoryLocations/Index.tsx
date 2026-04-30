import InventoryTabs from '@/Components/InventoryTabs';
import AdminLayout from '@/Layouts/AdminLayout';
import { Link } from '@inertiajs/react';

type Props = { locations: { data: { id: number; name: string; slug: string; responsible_user_id: number | null; responsible_user?: { name: string } | null; is_active: boolean }[] } };

export default function InventoryLocationsIndex({ locations }: Props) {
    return (
        <AdminLayout title="Localizacoes de Inventario" subtitle="Gestao de localizacoes de armazem" headerActions={<Link href={route('admin.inventory-locations.create')} className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white">Nova Localizacao</Link>}>
            <InventoryTabs />
            <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                <table className="min-w-full divide-y divide-slate-200 text-sm">
                    <thead className="bg-slate-50 text-left text-slate-600"><tr><th className="px-4 py-3">Nome</th><th className="px-4 py-3">Responsavel</th><th className="px-4 py-3">Ativa</th></tr></thead>
                    <tbody className="divide-y divide-slate-100">
                        {locations.data.map((item) => (
                            <tr key={item.id}><td className="px-4 py-3"><Link href={route('admin.inventory-locations.edit', item.id)}>{item.name}</Link></td><td className="px-4 py-3">{item.responsible_user?.name ?? '-'}</td><td className="px-4 py-3">{item.is_active ? 'Sim' : 'Nao'}</td></tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AdminLayout>
    );
}
