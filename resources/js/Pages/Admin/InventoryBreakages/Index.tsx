import InventoryBreakageStatusBadge from '@/Components/InventoryBreakageStatusBadge';
import InventoryFilters from '@/Components/InventoryFilters';
import InventoryTabs from '@/Components/InventoryTabs';
import AdminLayout from '@/Layouts/AdminLayout';
import { Link } from '@inertiajs/react';

type Props = { breakages: { data: any[] }; filters: Record<string, string | boolean | undefined> };

export default function InventoryBreakagesIndex({ breakages, filters }: Props) {
    return (
        <AdminLayout title="Quebras" subtitle="Gestao de perdas, danos e quebras" headerActions={<Link href={route('admin.inventory-breakages.create')} className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white">Reportar Quebra</Link>}>
            <InventoryTabs />
            <InventoryFilters indexRouteName="admin.inventory-breakages.index" initialFilters={filters} />
            <div className="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white">
                <table className="min-w-full divide-y divide-slate-200 text-sm">
                    <thead className="bg-slate-50 text-left text-slate-600"><tr><th className="px-4 py-3">Item</th><th className="px-4 py-3">Qtd</th><th className="px-4 py-3">Tipo</th><th className="px-4 py-3">Estado</th><th className="px-4 py-3">Reportado por</th></tr></thead>
                    <tbody className="divide-y divide-slate-100">
                        {breakages.data.map((breakage) => (
                            <tr key={breakage.id}><td className="px-4 py-3"><Link href={route('admin.inventory-breakages.show', breakage.id)}>{breakage.item?.name ?? '-'}</Link></td><td className="px-4 py-3">{breakage.quantity}</td><td className="px-4 py-3">{breakage.breakage_type}</td><td className="px-4 py-3"><InventoryBreakageStatusBadge status={breakage.status} /></td><td className="px-4 py-3">{breakage.reporter?.name ?? '-'}</td></tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AdminLayout>
    );
}
