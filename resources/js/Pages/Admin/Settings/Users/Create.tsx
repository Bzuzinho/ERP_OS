import AppCard from '@/Components/App/AppCard';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';

type Organization = { id: number; name: string };
type Role = { id: number; name: string };

type Props = {
    organizations: Organization[];
    roles: Role[];
};

const portalRoles = ['cidadao', 'associacao', 'empresa'];
const internalRoles = ['super_admin', 'admin_junta', 'executivo', 'administrativo', 'operacional', 'manutencao', 'armazem', 'rh'];

export default function UsersCreate({ organizations, roles }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        password: '',
        organization_id: '',
        nif: '',
        phone: '',
        address: '',
        birth_date: '',
        roles: [] as string[],
        is_active: true,
    });

    const toggleRole = (name: string) => {
        setData('roles', data.roles.includes(name)
            ? data.roles.filter((r) => r !== name)
            : [...data.roles, name]);
    };

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('admin.settings.users.store'));
    };

    return (
        <AdminLayout title="Novo Utilizador" subtitle="Criar conta de acesso à plataforma">
            <Head title="Novo Utilizador" />
            <div className="max-w-2xl">
                <form onSubmit={submit} className="space-y-5">
                    <AppCard>
                        <h2 className="mb-4 text-sm font-semibold text-slate-700 uppercase tracking-wide">Dados do utilizador</h2>
                        <div className="space-y-4">
                            <div>
                                <label className="block text-sm font-medium text-slate-700 mb-1">Nome *</label>
                                <input
                                    type="text"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    className="w-full rounded-2xl border border-slate-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required
                                />
                                {errors.name && <p className="mt-1 text-xs text-red-600">{errors.name}</p>}
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-slate-700 mb-1">Email *</label>
                                <input
                                    type="email"
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    className="w-full rounded-2xl border border-slate-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required
                                />
                                {errors.email && <p className="mt-1 text-xs text-red-600">{errors.email}</p>}
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-slate-700 mb-1">
                                    Password <span className="text-slate-400">(deixar em branco para gerar automaticamente)</span>
                                </label>
                                <input
                                    type="password"
                                    value={data.password}
                                    onChange={(e) => setData('password', e.target.value)}
                                    className="w-full rounded-2xl border border-slate-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    autoComplete="new-password"
                                />
                                {errors.password && <p className="mt-1 text-xs text-red-600">{errors.password}</p>}
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-slate-700 mb-1">Organização</label>
                                <select
                                    value={data.organization_id}
                                    onChange={(e) => setData('organization_id', e.target.value)}
                                    className="w-full rounded-2xl border border-slate-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                    <option value="">— Sem organização —</option>
                                    {organizations.map((org) => (
                                        <option key={org.id} value={org.id}>{org.name}</option>
                                    ))}
                                </select>
                                {errors.organization_id && <p className="mt-1 text-xs text-red-600">{errors.organization_id}</p>}
                            </div>

                            <div className="flex items-center gap-3">
                                <input
                                    id="is_active"
                                    type="checkbox"
                                    checked={data.is_active}
                                    onChange={(e) => setData('is_active', e.target.checked)}
                                    className="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                />
                                <label htmlFor="is_active" className="text-sm text-slate-700">Conta ativa</label>
                            </div>
                        </div>
                    </AppCard>

                    <AppCard>
                        <h2 className="mb-4 text-sm font-semibold text-slate-700 uppercase tracking-wide">Dados pessoais</h2>
                        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label className="mb-1 block text-sm font-medium text-slate-700">NIF</label>
                                <input
                                    type="text"
                                    value={data.nif}
                                    onChange={(e) => setData('nif', e.target.value)}
                                    placeholder="000000000"
                                    className="w-full rounded-2xl border border-slate-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                                {errors.nif && <p className="mt-1 text-xs text-red-600">{errors.nif}</p>}
                            </div>

                            <div>
                                <label className="mb-1 block text-sm font-medium text-slate-700">Contacto</label>
                                <input
                                    type="text"
                                    value={data.phone}
                                    onChange={(e) => setData('phone', e.target.value)}
                                    placeholder="+351 900 000 000"
                                    className="w-full rounded-2xl border border-slate-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                                {errors.phone && <p className="mt-1 text-xs text-red-600">{errors.phone}</p>}
                            </div>

                            <div>
                                <label className="mb-1 block text-sm font-medium text-slate-700">Data de nascimento</label>
                                <input
                                    type="date"
                                    value={data.birth_date}
                                    onChange={(e) => setData('birth_date', e.target.value)}
                                    className="w-full rounded-2xl border border-slate-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                                {errors.birth_date && <p className="mt-1 text-xs text-red-600">{errors.birth_date}</p>}
                            </div>

                            <div className="sm:col-span-2">
                                <label className="mb-1 block text-sm font-medium text-slate-700">Morada</label>
                                <textarea
                                    value={data.address}
                                    onChange={(e) => setData('address', e.target.value)}
                                    rows={2}
                                    placeholder="Rua, número, localidade"
                                    className="w-full resize-none rounded-2xl border border-slate-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                                {errors.address && <p className="mt-1 text-xs text-red-600">{errors.address}</p>}
                            </div>
                        </div>
                    </AppCard>

                    <AppCard>
                        <h2 className="mb-4 text-sm font-semibold text-slate-700 uppercase tracking-wide">Perfis</h2>
                        {errors.roles && <p className="mb-2 text-xs text-red-600">{errors.roles}</p>}

                        <div className="space-y-3">
                            <p className="text-xs font-semibold text-slate-500 uppercase tracking-wide">Internos</p>
                            <div className="flex flex-wrap gap-2">
                                {roles.filter((r) => internalRoles.includes(r.name)).map((role) => (
                                    <button
                                        key={role.id}
                                        type="button"
                                        onClick={() => toggleRole(role.name)}
                                        className={`rounded-2xl border px-3 py-1.5 text-xs font-semibold transition-colors ${
                                            data.roles.includes(role.name)
                                                ? 'border-blue-500 bg-blue-50 text-blue-700'
                                                : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300'
                                        }`}
                                    >
                                        {role.name}
                                    </button>
                                ))}
                            </div>

                            <p className="mt-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Portal</p>
                            <div className="flex flex-wrap gap-2">
                                {roles.filter((r) => portalRoles.includes(r.name)).map((role) => (
                                    <button
                                        key={role.id}
                                        type="button"
                                        onClick={() => toggleRole(role.name)}
                                        className={`rounded-2xl border px-3 py-1.5 text-xs font-semibold transition-colors ${
                                            data.roles.includes(role.name)
                                                ? 'border-amber-500 bg-amber-50 text-amber-700'
                                                : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300'
                                        }`}
                                    >
                                        {role.name}
                                    </button>
                                ))}
                            </div>
                        </div>
                    </AppCard>

                    <div className="flex justify-end gap-3">
                        <Link
                            href={route('admin.settings.users.index')}
                            className="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                        >
                            Cancelar
                        </Link>
                        <button
                            type="submit"
                            disabled={processing}
                            className="rounded-2xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-60"
                        >
                            Criar utilizador
                        </button>
                    </div>
                </form>
            </div>
        </AdminLayout>
    );
}
