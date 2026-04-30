import InventoryFilters from '@/Components/InventoryFilters';
import InventoryLoanStatusBadge from '@/Components/InventoryLoanStatusBadge';
import InventoryTabs from '@/Components/InventoryTabs';
import AdminLayout from '@/Layouts/AdminLayout';
import { Link } from '@inertiajs/react';

type Props = { loans: { data: any[] }; filters: Record<string, string | boolean | undefined> };

export default function InventoryLoansIndex({ loans, filters }: Props) {
    return (
        <AdminLayout title="Emprestimos" subtitle="Emprestimos e devolucoes de equipamentos" headerActions={<Link href={route('admin.inventory-loans.create')} className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white">Novo Emprestimo</Link>}>
            <InventoryTabs />
            <InventoryFilters indexRouteName="admin.inventory-loans.index" initialFilters={filters} />
            <div className="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white">
                <table className="min-w-full divide-y divide-slate-200 text-sm">
                    <thead className="bg-slate-50 text-left text-slate-600"><tr><th className="px-4 py-3">Item</th><th className="px-4 py-3">Requerente</th><th className="px-4 py-3">Quantidade</th><th className="px-4 py-3">Emprestado em</th><th className="px-4 py-3">Prev. devolucao</th><th className="px-4 py-3">Estado</th></tr></thead>
                    <tbody className="divide-y divide-slate-100">
                        {loans.data.map((loan) => (
                            <tr key={loan.id}><td className="px-4 py-3"><Link href={route('admin.inventory-loans.show', loan.id)}>{loan.item?.name ?? '-'}</Link></td><td className="px-4 py-3">{loan.borrower_user?.name ?? loan.borrower_contact?.name ?? '-'}</td><td className="px-4 py-3">{loan.quantity}</td><td className="px-4 py-3">{loan.loaned_at ? new Date(loan.loaned_at).toLocaleDateString() : '-'}</td><td className="px-4 py-3">{loan.expected_return_at ? new Date(loan.expected_return_at).toLocaleDateString() : '-'}</td><td className="px-4 py-3"><InventoryLoanStatusBadge status={loan.status} /></td></tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AdminLayout>
    );
}
