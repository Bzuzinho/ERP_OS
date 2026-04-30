import InventoryTabs from '@/Components/InventoryTabs';
import AdminLayout from '@/Layouts/AdminLayout';
import { Link } from '@inertiajs/react';

type Props = { categories: { data: { id: number; name: string; slug: string; is_active: boolean }[] } };

export default function InventoryCategoriesIndex({ categories }: Props) {
    return (
        <AdminLayout title="Categorias de Inventario" subtitle="Gestao de categorias do armazem" headerActions={<Link href={route('admin.inventory-categories.create')} className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white">Nova Categoria</Link>}>
            <InventoryTabs />
            <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                <table className="min-w-full divide-y divide-slate-200 text-sm">
                    <thead className="bg-slate-50 text-left text-slate-600"><tr><th className="px-4 py-3">Nome</th><th className="px-4 py-3">Slug</th><th className="px-4 py-3">Ativa</th></tr></thead>
                    <tbody className="divide-y divide-slate-100">
                        {categories.data.map((item) => (
                            <tr key={item.id}><td className="px-4 py-3"><Link href={route('admin.inventory-categories.edit', item.id)}>{item.name}</Link></td><td className="px-4 py-3">{item.slug}</td><td className="px-4 py-3">{item.is_active ? 'Sim' : 'Nao'}</td></tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AdminLayout>
    );
}
