import AppBadge from '@/Components/App/AppBadge';
import AppCard from '@/Components/App/AppCard';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import type { PageProps } from '@/types';
import { useState } from 'react';

type Role = { id: number; name: string };
type Organization = { id: number; name: string };

type UserDetail = {
    id: number;
    name: string;
    email: string;
    is_active: boolean;
    organization?: Organization | null;
    roles: Role[];
    updated_at: string;
    created_at: string;
};

type Props = {
    user: UserDetail;
    organizations: Organization[];
    roles: Role[];
    canManageRoles: boolean;
    isSuperAdmin: boolean;
};

const portalRoles = ['cidadao', 'associacao', 'empresa'];
const internalRoles = ['super_admin', 'admin_junta', 'executivo', 'administrativo', 'operacional', 'manutencao', 'armazem', 'rh'];

export default function UsersEdit({ user, organizations, roles, canManageRoles, isSuperAdmin }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        name: user.name,
        email: user.email,
        organization_id: user.organization?.id?.toString() ?? '',
        is_active: user.is_active,
    });

    const [selectedRoles, setSelectedRoles] = useState<string[]>(user.roles.map((r) => r.name));
    const [rolesProcessing, setRolesProcessing] = useState(false);

    const toggleRole = (name: string) => {
        if (name === 'super_admin' && !isSuperAdmin) return;
        setSelectedRoles((prev) =>
            prev.includes(name) ? prev.filter((r) => r !== name) : [...prev, name],
        );
    };

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        put(route('admin.settings.users.update', user.id));
    };

    const saveRoles = () => {
        setRolesProcessing(true);
        router.post(
            route('admin.settings.users.update-roles', user.id),
            { roles: selectedRoles },
            { onFinish: () => setRolesProcessing(false) },
        );
    };

    return (
        <AdminLayout title={`Editar: ${user.name}`} subtitle="Alterar dados do utilizador">
            <Head title={`Editar: ${user.name}`} />

            <div className="max-w-2xl space-y-5">
                {/* Main info form */}
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

                    <div className="flex justify-end gap-3">
                        <Link
                            href={route('admin.settings.users.show', user.id)}
                            className="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                        >
                            Cancelar
                        </Link>
                        <button
                            type="submit"
                            disabled={processing}
                            className="rounded-2xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-60"
                        >
                            Guardar alterações
                        </button>
                    </div>
                </form>

                {/* Roles section */}
                {canManageRoles && (
                    <AppCard>
                        <h2 className="mb-4 text-sm font-semibold text-slate-700 uppercase tracking-wide">Perfis</h2>

                        <div className="space-y-3">
                            <p className="text-xs font-semibold text-slate-500 uppercase tracking-wide">Internos</p>
                            <div className="flex flex-wrap gap-2">
                                {roles.filter((r) => internalRoles.includes(r.name)).map((role) => {
                                    const isSuperAdminRole = role.name === 'super_admin';
                                    const isSelected = selectedRoles.includes(role.name);
                                    const isDisabled = isSuperAdminRole && !isSuperAdmin;
                                    return (
                                        <button
                                            key={role.id}
                                            type="button"
                                            onClick={() => toggleRole(role.name)}
                                            disabled={isDisabled}
                                            title={isDisabled ? 'Apenas o super_admin pode atribuir este perfil' : undefined}
                                            className={`rounded-2xl border px-3 py-1.5 text-xs font-semibold transition-colors disabled:opacity-40 disabled:cursor-not-allowed ${
                                                isSelected
                                                    ? 'border-blue-500 bg-blue-50 text-blue-700'
                                                    : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300'
                                            }`}
                                        >
                                            {role.name}
                                        </button>
                                    );
                                })}
                            </div>

                            <p className="text-xs font-semibold text-slate-500 uppercase tracking-wide mt-2">Portal</p>
                            <div className="flex flex-wrap gap-2">
                                {roles.filter((r) => portalRoles.includes(r.name)).map((role) => {
                                    const isSelected = selectedRoles.includes(role.name);
                                    return (
                                        <button
                                            key={role.id}
                                            type="button"
                                            onClick={() => toggleRole(role.name)}
                                            className={`rounded-2xl border px-3 py-1.5 text-xs font-semibold transition-colors ${
                                                isSelected
                                                    ? 'border-amber-500 bg-amber-50 text-amber-700'
                                                    : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300'
                                            }`}
                                        >
                                            {role.name}
                                        </button>
                                    );
                                })}
                            </div>
                        </div>

                        <div className="mt-4 flex justify-end">
                            <button
                                type="button"
                                onClick={saveRoles}
                                disabled={rolesProcessing}
                                className="rounded-2xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-60"
                            >
                                Guardar perfis
                            </button>
                        </div>
                    </AppCard>
                )}
            </div>
        </AdminLayout>
    );
}
