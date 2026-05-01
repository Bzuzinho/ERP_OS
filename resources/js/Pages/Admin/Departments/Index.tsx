import AdminLayout from '@/Layouts/AdminLayout';
import { Link } from '@inertiajs/react';

type Department = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    is_active: boolean;
    employees_count: number;
    teams_count: number;
};

type Props = {
    departments: {
        data: Department[];
        links: { url: string | null; label: string; active: boolean }[];
    };
};

export default function DepartmentsIndex({ departments }: Props) {
    return (
        <AdminLayout
            title="Departamentos"
            subtitle="Gestão dos departamentos da organização"
            headerActions={
                <Link href={route('admin.hr.departments.create')} className="inline-flex rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                    Novo Departamento
                </Link>
            }
        >
            <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                <table className="min-w-full divide-y divide-slate-200 text-sm">
                    <thead className="bg-slate-50 text-left text-slate-600">
                        <tr>
                            <th className="px-4 py-3">Nome</th>
                            <th className="px-4 py-3">Funcionários</th>
                            <th className="px-4 py-3">Equipas</th>
                            <th className="px-4 py-3">Estado</th>
                            <th className="px-4 py-3">Ações</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                        {departments.data.length === 0 && (
                            <tr>
                                <td colSpan={5} className="px-4 py-6 text-center text-slate-500">
                                    Nenhum departamento criado.
                                </td>
                            </tr>
                        )}
                        {departments.data.map((dept) => (
                            <tr key={dept.id}>
                                <td className="px-4 py-3 font-medium text-slate-900">{dept.name}</td>
                                <td className="px-4 py-3 text-slate-700">{dept.employees_count}</td>
                                <td className="px-4 py-3 text-slate-700">{dept.teams_count}</td>
                                <td className="px-4 py-3">
                                    <span className={`inline-flex rounded-full px-2 py-0.5 text-xs font-medium ${dept.is_active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600'}`}>
                                        {dept.is_active ? 'Ativo' : 'Inativo'}
                                    </span>
                                </td>
                                <td className="px-4 py-3">
                                    <div className="flex gap-3">
                                        <Link href={route('admin.hr.departments.show', dept.id)} className="text-slate-700 hover:text-slate-950">Ver</Link>
                                        <Link href={route('admin.hr.departments.edit', dept.id)} className="text-slate-700 hover:text-slate-950">Editar</Link>
                                    </div>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            {departments.links.length > 3 && (
                <div className="mt-4 flex gap-1">
                    {departments.links.map((link, i) => (
                        link.url ? (
                            <Link key={i} href={link.url} className={`rounded px-3 py-1 text-sm ${link.active ? 'bg-slate-900 text-white' : 'text-slate-700 hover:bg-slate-100'}`} dangerouslySetInnerHTML={{ __html: link.label }} />
                        ) : (
                            <span key={i} className="rounded px-3 py-1 text-sm text-slate-400" dangerouslySetInnerHTML={{ __html: link.label }} />
                        )
                    ))}
                </div>
            )}
        </AdminLayout>
    );
}
