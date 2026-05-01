import AdminLayout from '@/Layouts/AdminLayout';
import { Link, router } from '@inertiajs/react';

type AbsenceType = { id: number; name: string; slug: string; max_days_per_year: number | null; requires_proof: boolean; is_paid: boolean; is_active: boolean };

type Props = {
    types: { data: AbsenceType[]; links: { url: string | null; label: string; active: boolean }[] };
    filters: { status?: string };
};

export default function AbsenceTypesIndex({ types, filters }: Props) {
    return (
        <AdminLayout
            title="Tipos de Ausência"
            subtitle="Gestão dos tipos de ausência e licença"
            headerActions={
                <Link href={route('admin.hr.absence-types.create')} className="inline-flex rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                    Novo Tipo
                </Link>
            }
        >
            <div className="mb-4 flex gap-2 rounded-2xl border border-slate-200 bg-white p-4">
                <select defaultValue={filters.status ?? ''} onChange={(e) => router.get(route('admin.hr.absence-types.index'), { status: e.target.value || undefined }, { preserveState: true, replace: true })} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Todos os estados</option>
                    <option value="active">Ativo</option>
                    <option value="inactive">Inativo</option>
                </select>
            </div>

            <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                <table className="min-w-full divide-y divide-slate-200 text-sm">
                    <thead className="bg-slate-50 text-left text-slate-600">
                        <tr>
                            <th className="px-4 py-3">Nome</th>
                            <th className="px-4 py-3">Dias máx./ano</th>
                            <th className="px-4 py-3">Requer prova</th>
                            <th className="px-4 py-3">Remunerado</th>
                            <th className="px-4 py-3">Estado</th>
                            <th className="px-4 py-3">Ações</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                        {types.data.length === 0 && <tr><td colSpan={6} className="px-4 py-6 text-center text-slate-500">Nenhum tipo de ausência criado.</td></tr>}
                        {types.data.map((t) => (
                            <tr key={t.id}>
                                <td className="px-4 py-3 font-medium text-slate-900">{t.name}</td>
                                <td className="px-4 py-3 text-slate-700">{t.max_days_per_year ?? 'Ilimitado'}</td>
                                <td className="px-4 py-3 text-slate-700">{t.requires_proof ? 'Sim' : 'Não'}</td>
                                <td className="px-4 py-3 text-slate-700">{t.is_paid ? 'Sim' : 'Não'}</td>
                                <td className="px-4 py-3">
                                    <span className={`inline-flex rounded-full px-2 py-0.5 text-xs font-medium ${t.is_active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600'}`}>
                                        {t.is_active ? 'Ativo' : 'Inativo'}
                                    </span>
                                </td>
                                <td className="px-4 py-3">
                                    <Link href={route('admin.hr.absence-types.edit', t.id)} className="text-slate-700 hover:text-slate-950">Editar</Link>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AdminLayout>
    );
}
