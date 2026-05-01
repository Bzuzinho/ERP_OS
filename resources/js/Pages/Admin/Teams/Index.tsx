import AdminLayout from '@/Layouts/AdminLayout';
import { Link, router } from '@inertiajs/react';

type Team = {
    id: number;
    name: string;
    is_active: boolean;
    department: { id: number; name: string } | null;
    team_members: { id: number }[];
};

type DeptOption = { [key: number]: string };

type Props = {
    teams: { data: Team[]; links: { url: string | null; label: string; active: boolean }[] };
    departments: DeptOption;
    filters: { department?: string; status?: string; search?: string };
};

export default function TeamsIndex({ teams, departments, filters }: Props) {
    const filter = (key: string, value: string) =>
        router.get(route('admin.hr.teams.index'), { ...filters, [key]: value || undefined }, { preserveState: true, replace: true });

    return (
        <AdminLayout
            title="Equipas"
            subtitle="Gestão das equipas de trabalho"
            headerActions={
                <Link href={route('admin.hr.teams.create')} className="inline-flex rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                    Nova Equipa
                </Link>
            }
        >
            <div className="mb-4 flex flex-wrap gap-2 rounded-2xl border border-slate-200 bg-white p-4">
                <input type="text" defaultValue={filters.search ?? ''} onChange={(e) => filter('search', e.target.value)} placeholder="Pesquisar" className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                <select defaultValue={filters.department ?? ''} onChange={(e) => filter('department', e.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Todos os departamentos</option>
                    {Object.entries(departments).map(([id, name]) => <option key={id} value={id}>{name}</option>)}
                </select>
                <select defaultValue={filters.status ?? ''} onChange={(e) => filter('status', e.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
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
                            <th className="px-4 py-3">Departamento</th>
                            <th className="px-4 py-3">Membros</th>
                            <th className="px-4 py-3">Estado</th>
                            <th className="px-4 py-3">Ações</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                        {teams.data.length === 0 && <tr><td colSpan={5} className="px-4 py-6 text-center text-slate-500">Nenhuma equipa encontrada.</td></tr>}
                        {teams.data.map((team) => (
                            <tr key={team.id}>
                                <td className="px-4 py-3 font-medium text-slate-900">{team.name}</td>
                                <td className="px-4 py-3 text-slate-700">{team.department?.name ?? '-'}</td>
                                <td className="px-4 py-3 text-slate-700">{team.team_members.length}</td>
                                <td className="px-4 py-3">
                                    <span className={`inline-flex rounded-full px-2 py-0.5 text-xs font-medium ${team.is_active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600'}`}>
                                        {team.is_active ? 'Ativa' : 'Inativa'}
                                    </span>
                                </td>
                                <td className="px-4 py-3">
                                    <div className="flex gap-3">
                                        <Link href={route('admin.hr.teams.show', team.id)} className="text-slate-700 hover:text-slate-950">Ver</Link>
                                        <Link href={route('admin.hr.teams.edit', team.id)} className="text-slate-700 hover:text-slate-950">Editar</Link>
                                    </div>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AdminLayout>
    );
}
