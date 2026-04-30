import InventoryLoanStatusBadge from '@/Components/InventoryLoanStatusBadge';
import InventoryTabs from '@/Components/InventoryTabs';
import AdminLayout from '@/Layouts/AdminLayout';
import { useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type Props = { loan: any; canReturn: boolean };

export default function InventoryLoansShow({ loan, canReturn }: Props) {
    const form = useForm({ return_notes: '' });

    const submitReturn = (event: FormEvent) => {
        event.preventDefault();
        form.post(route('admin.inventory-loans.return', loan.id));
    };

    return (
        <AdminLayout title="Detalhe de Emprestimo" subtitle="Estado e historico do emprestimo">
            <InventoryTabs />
            <div className="space-y-4">
                <div className="rounded-2xl border border-slate-200 bg-white p-4 text-sm text-slate-700">
                    <p><strong>Item:</strong> {loan.item?.name ?? '-'}</p>
                    <p><strong>Requerente:</strong> {loan.borrower_user?.name ?? loan.borrower_contact?.name ?? '-'}</p>
                    <p><strong>Quantidade:</strong> {loan.quantity}</p>
                    <p><strong>Estado:</strong> <InventoryLoanStatusBadge status={loan.status} /></p>
                </div>
                {canReturn && ['active', 'overdue'].includes(loan.status) ? (
                    <form onSubmit={submitReturn} className="rounded-2xl border border-slate-200 bg-white p-4">
                        <textarea value={form.data.return_notes} onChange={(event) => form.setData('return_notes', event.target.value)} placeholder="Notas de devolucao" className="min-h-20 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                        <button className="mt-3 rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white">Registar Devolucao</button>
                    </form>
                ) : null}
            </div>
        </AdminLayout>
    );
}
