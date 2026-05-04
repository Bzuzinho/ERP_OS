import AppBadge from '@/Components/App/AppBadge';
import AppCard from '@/Components/App/AppCard';
import EmptyState from '@/Components/App/EmptyState';
import FilterPills from '@/Components/App/FilterPills';
import FloatingActionButton from '@/Components/App/FloatingActionButton';
import PageHeader from '@/Components/App/PageHeader';
import SearchInput from '@/Components/App/SearchInput';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

type Role = { id: number; name: string };

type UserRow = {
    id: number;
    name: string;
    email: string;
    is_active: boolean;
    organization?: { id: number; name: string } | null;
    roles: Role[];
    updated_at: string;
    created_at: string;
};

type PaginatedUsers = {
    data: UserRow[];
    links: { url: string | null; label: string; active: boolean }[];
    meta?: { current_page: number; last_page: number; total: number };
    from?: number;
    to?: number;
    total?: number;
};

type Props = {
    users: PaginatedUsers;
    filters: { search?: string; status?: string; type?: string };
};

const statusTone = (isActive: boolean): 'green' | 'red' => (isActive ? 'green' : 'red');
const statusLabel = (isActive: boolean) => (isActive ? 'Ativo' : 'Inativo');

const roleTone = (name: string): 'blue' | 'indigo' | 'amber' | 'green' | 'slate' | 'red' => {
    if (name === 'super_admin') return 'red';
    if (name === 'admin_junta') return 'indigo';
    if (name === 'executivo') return 'blue';
    if (name === 'rh') return 'green';
    if (['cidadao', 'associacao', 'empresa'].includes(name)) return 'amber';
    return 'slate';
};

export default function UsersIndex({ users, filters }: Props) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [statusFilter, setStatusFilter] = useState(filters.status ?? '');
    const [typeFilter, setTypeFilter] = useState(filters.type ?? '');

    const applyFilters = (overrides: Partial<typeof filters> = {}) => {
        router.get(
            route('admin.settings.users.index'),
            {
                search: (overrides.search ?? search) || undefined,
                status: (overrides.status ?? statusFilter) || undefined,
                type: (overrides.type ?? typeFilter) || undefined,
            },
            { preserveState: true, replace: true },
        );
    };

    return (
        <AdminLayout
            title="Utilizadores"
            subtitle="Gestão de acessos à plataforma"
            headerActions={
                <Link
                    href={route('admin.settings.users.create')}
                    className="hidden rounded-2xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 lg:inline-flex"
                >
                    Novo utilizador
                </Link>
            }
        >
            <Head title="Utilizadores" />

            <div className="space-y-4">
                {/* Mobile filter pills */}
                <div className="lg:hidden">
                    <FilterPills
                        selected={statusFilter}
                        onChange={(v) => { setStatusFilter(v); applyFilters({ status: v }); }}
                        options={[
                            { label: 'Todos', value: '' },
                            { label: 'Ativos', value: 'active' },
                            { label: 'Inativos', value: 'inactive' },
                            { label: 'Internos', value: 'internal' },
                            { label: 'Portal', value: 'portal' },
                        ]}
                    />
                </div>

                {/* Desktop filters */}
                <AppCard className="hidden lg:block">
                    <div className="flex flex-wrap gap-3">
                        <div className="flex-1 min-w-[200px]">
                            <SearchInput
                                value={search}
                                onChange={setSearch}
                                placeholder="Pesquisar por nome ou email"
                            />
                        </div>
                        <select
                            value={statusFilter}
                            onChange={(e) => setStatusFilter(e.target.value)}
                            className="rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700"
                        >
                            <option value="">Todos os estados</option>
                            <option value="active">Ativos</option>
                            <option value="inactive">Inativos</option>
                        </select>
                        <select
                            value={typeFilter}
                            onChange={(e) => setTypeFilter(e.target.value)}
                            className="rounded-2xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700"
                        >
                            <option value="">Todos os tipos</option>
                            <option value="internal">Internos</option>
                            <option value="portal">Portal</option>
                        </select>
                        <button
                            type="button"
                            onClick={() => applyFilters()}
                            className="rounded-2xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700"
                        >
                            Filtrar
                        </button>
                    </div>
                </AppCard>

                {/* Table - desktop */}
                <AppCard className="hidden lg:block overflow-hidden p-0">
                    {users.data.length === 0 ? (
                        <div className="p-8">
                            <EmptyState
                                title="Nenhum utilizador encontrado"
                                description="Ajuste os filtros ou crie um novo utilizador."
                            />
                        </div>
                    ) : (
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="border-b border-slate-100 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                    <th className="px-5 py-3">Nome</th>
                                    <th className="px-5 py-3">Email</th>
                                    <th className="px-5 py-3">Organização</th>
                                    <th className="px-5 py-3">Perfis</th>
                                    <th className="px-5 py-3">Estado</th>
                                    <th className="px-5 py-3">Atualizado</th>
                                    <th className="px-5 py-3"></th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-50">
                                {users.data.map((user) => (
                                    <tr key={user.id} className="hover:bg-slate-50/50">
                                        <td className="px-5 py-3 font-medium text-slate-800">{user.name}</td>
                                        <td className="px-5 py-3 text-slate-600">{user.email}</td>
                                        <td className="px-5 py-3 text-slate-500">{user.organization?.name ?? '—'}</td>
                                        <td className="px-5 py-3">
                                            <div className="flex flex-wrap gap-1">
                                                {user.roles.map((r) => (
                                                    <AppBadge key={r.id} tone={roleTone(r.name)}>{r.name}</AppBadge>
                                                ))}
                                                {user.roles.length === 0 && <span className="text-slate-400">—</span>}
                                            </div>
                                        </td>
                                        <td className="px-5 py-3">
                                            <AppBadge tone={statusTone(user.is_active)}>{statusLabel(user.is_active)}</AppBadge>
                                        </td>
                                        <td className="px-5 py-3 text-slate-400 text-xs">
                                            {new Date(user.updated_at).toLocaleDateString('pt-PT')}
                                        </td>
                                        <td className="px-5 py-3">
                                            <Link
                                                href={route('admin.settings.users.show', user.id)}
                                                className="text-blue-600 hover:underline text-xs font-semibold"
                                            >
                                                Ver
                                            </Link>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    )}
                </AppCard>

                {/* Cards - mobile */}
                <div className="space-y-3 lg:hidden">
                    {users.data.length === 0 ? (
                        <EmptyState
                            title="Nenhum utilizador encontrado"
                            description="Ajuste os filtros ou crie um novo utilizador."
                        />
                    ) : (
                        users.data.map((user) => (
                            <Link key={user.id} href={route('admin.settings.users.show', user.id)}>
                                <AppCard className="flex items-start justify-between gap-3">
                                    <div className="min-w-0">
                                        <p className="font-semibold text-slate-800 truncate">{user.name}</p>
                                        <p className="text-sm text-slate-500 truncate">{user.email}</p>
                                        <p className="mt-1 text-xs text-slate-400">{user.organization?.name ?? '—'}</p>
                                        <div className="mt-2 flex flex-wrap gap-1">
                                            {user.roles.map((r) => (
                                                <AppBadge key={r.id} tone={roleTone(r.name)}>{r.name}</AppBadge>
                                            ))}
                                        </div>
                                    </div>
                                    <AppBadge tone={statusTone(user.is_active)}>{statusLabel(user.is_active)}</AppBadge>
                                </AppCard>
                            </Link>
                        ))
                    )}
                </div>

                {/* Pagination */}
                {users.links && users.links.length > 3 && (
                    <div className="flex justify-center gap-1">
                        {users.links.map((link, i) => (
                            <Link
                                key={i}
                                href={link.url ?? '#'}
                                className={`rounded-xl px-3 py-1.5 text-sm ${link.active ? 'bg-blue-600 text-white font-semibold' : 'text-slate-600 hover:bg-slate-100'} ${!link.url ? 'pointer-events-none opacity-30' : ''}`}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        ))}
                    </div>
                )}
            </div>

            <FloatingActionButton href={route('admin.settings.users.create')} label="Novo utilizador" />
        </AdminLayout>
    );
}
