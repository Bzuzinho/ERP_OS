import AppBadge from '@/Components/App/AppBadge';
import AppCard from '@/Components/App/AppCard';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { useState } from 'react';

type Role = { id: number; name: string };

type UserDetail = {
    id: number;
    name: string;
    email: string;
    is_active: boolean;
    organization?: { id: number; name: string } | null;
    roles: Role[];
    updated_at: string;
    created_at: string;
    nif?: string | null;
    phone?: string | null;
    address?: string | null;
    birth_date?: string | null;
    avatar_path?: string | null;
};

type Can = {
    update: boolean;
    manageRoles: boolean;
    resetPassword: boolean;
    activate: boolean;
    deactivate: boolean;
};

type Props = {
    user: UserDetail;
    effectivePermissions: string[];
    can: Can;
};

const MODULES = [
    'settings', 'users', 'roles', 'admin',
    'contacts', 'tickets', 'tasks', 'events',
    'documents', 'document_types', 'meeting_minutes',
    'spaces', 'inventory', 'hr', 'planning', 'reports',
];

function groupPermissions(permissions: string[]): Record<string, string[]> {
    const grouped: Record<string, string[]> = {};
    for (const perm of permissions) {
        const dot = perm.indexOf('.');
        const module = dot !== -1 ? perm.substring(0, dot) : perm;
        if (!grouped[module]) grouped[module] = [];
        grouped[module].push(perm);
    }
    return grouped;
}

const roleTone = (name: string): 'blue' | 'indigo' | 'amber' | 'green' | 'slate' | 'red' => {
    if (name === 'super_admin') return 'red';
    if (name === 'admin_junta') return 'indigo';
    if (name === 'executivo') return 'blue';
    if (name === 'rh') return 'green';
    if (['cidadao', 'associacao', 'empresa'].includes(name)) return 'amber';
    return 'slate';
};

export default function UsersShow({ user, effectivePermissions, can }: Props) {
    const { data, setData, post, processing, errors, reset } = useForm({
        password: '',
        password_confirmation: '',
    });
    const [showPasswordForm, setShowPasswordForm] = useState(false);

    const grouped = groupPermissions(effectivePermissions);

    const submitPassword = (e: React.FormEvent) => {
        e.preventDefault();
        router.post(route('admin.settings.users.reset-password', user.id), {
            password: data.password,
            password_confirmation: data.password_confirmation,
        }, {
            onSuccess: () => { reset(); setShowPasswordForm(false); },
        });
    };

    const handleActivate = () => {
        router.post(route('admin.settings.users.activate', user.id));
    };

    const handleDeactivate = () => {
        if (confirm('Desativar este utilizador? Perderá acesso imediatamente.')) {
            router.post(route('admin.settings.users.deactivate', user.id));
        }
    };

    return (
        <AdminLayout
            title={user.name}
            subtitle="Detalhes do utilizador"
            headerActions={
                <div className="flex gap-2">
                    {can.update && (
                        <Link
                            href={route('admin.settings.users.edit', user.id)}
                            className="rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                        >
                            Editar
                        </Link>
                    )}
                    {can.deactivate && user.is_active && (
                        <button
                            type="button"
                            onClick={handleDeactivate}
                            className="rounded-2xl border border-red-200 bg-white px-3 py-2 text-sm font-semibold text-red-600 hover:bg-red-50"
                        >
                            Desativar
                        </button>
                    )}
                    {can.activate && !user.is_active && (
                        <button
                            type="button"
                            onClick={handleActivate}
                            className="rounded-2xl border border-emerald-200 bg-white px-3 py-2 text-sm font-semibold text-emerald-600 hover:bg-emerald-50"
                        >
                            Ativar
                        </button>
                    )}
                </div>
            }
        >
            <Head title={user.name} />

            <div className="max-w-3xl space-y-5">
                {/* Summary card */}
                <AppCard>
                    <div className="flex items-start gap-4 flex-wrap">
                        {/* Avatar */}
                        <div className="h-14 w-14 shrink-0 overflow-hidden rounded-full bg-slate-100 border border-slate-200">
                            {user.avatar_path ? (
                                <img src={`/storage/${user.avatar_path}`} alt="" className="h-full w-full object-cover" />
                            ) : (
                                <span className="flex h-full w-full items-center justify-center text-xl font-bold text-slate-400">
                                    {user.name.charAt(0).toUpperCase()}
                                </span>
                            )}
                        </div>
                        <div className="flex-1 space-y-1 min-w-0">
                            <div className="flex items-center gap-2 flex-wrap">
                                <p className="text-lg font-bold text-slate-800">{user.name}</p>
                                <AppBadge tone={user.is_active ? 'green' : 'red'}>
                                    {user.is_active ? 'Ativo' : 'Inativo'}
                                </AppBadge>
                            </div>
                            <p className="text-sm text-slate-500">{user.email}</p>
                            <p className="text-sm text-slate-400">{user.organization?.name ?? '—'}</p>
                        </div>
                    </div>

                    <div className="mt-4 flex flex-wrap gap-1">
                        {user.roles.map((r) => (
                            <AppBadge key={r.id} tone={roleTone(r.name)}>{r.name}</AppBadge>
                        ))}
                        {user.roles.length === 0 && <span className="text-xs text-slate-400">Sem perfis atribuídos</span>}
                    </div>

                    <div className="mt-4 flex flex-wrap gap-3 text-xs text-slate-400">
                        <span>Criado em: {new Date(user.created_at).toLocaleDateString('pt-PT')}</span>
                        <span>Atualizado em: {new Date(user.updated_at).toLocaleDateString('pt-PT')}</span>
                    </div>

                    {(user.nif || user.phone || user.address || user.birth_date) && (
                        <div className="mt-4 grid grid-cols-2 gap-x-6 gap-y-2 text-sm border-t border-slate-100 pt-4">
                            {user.nif && (
                                <div>
                                    <span className="text-xs font-semibold text-slate-400 uppercase tracking-wide">NIF</span>
                                    <p className="text-slate-700">{user.nif}</p>
                                </div>
                            )}
                            {user.phone && (
                                <div>
                                    <span className="text-xs font-semibold text-slate-400 uppercase tracking-wide">Contacto</span>
                                    <p className="text-slate-700">{user.phone}</p>
                                </div>
                            )}
                            {user.birth_date && (
                                <div>
                                    <span className="text-xs font-semibold text-slate-400 uppercase tracking-wide">Data de nascimento</span>
                                    <p className="text-slate-700">{new Date(user.birth_date).toLocaleDateString('pt-PT')}</p>
                                </div>
                            )}
                            {user.address && (
                                <div className="col-span-2">
                                    <span className="text-xs font-semibold text-slate-400 uppercase tracking-wide">Morada</span>
                                    <p className="text-slate-700">{user.address}</p>
                                </div>
                            )}
                        </div>
                    )}
                </AppCard>

                {/* Effective permissions */}
                <AppCard>
                    <h2 className="mb-3 text-sm font-semibold text-slate-700 uppercase tracking-wide">
                        Permissões efetivas ({effectivePermissions.length})
                    </h2>
                    {effectivePermissions.length === 0 ? (
                        <p className="text-sm text-slate-400">Nenhuma permissão atribuída.</p>
                    ) : (
                        <div className="space-y-3">
                            {Object.entries(grouped)
                                .sort(([a], [b]) => a.localeCompare(b))
                                .map(([module, perms]) => (
                                    <div key={module}>
                                        <p className="mb-1 text-xs font-semibold text-slate-500 uppercase tracking-wide">{module}</p>
                                        <div className="flex flex-wrap gap-1">
                                            {perms.map((p) => (
                                                <span key={p} className="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">
                                                    {p}
                                                </span>
                                            ))}
                                        </div>
                                    </div>
                                ))}
                        </div>
                    )}
                </AppCard>

                {/* Reset password */}
                {can.resetPassword && (
                    <AppCard>
                        <div className="flex items-center justify-between">
                            <div>
                                <h2 className="text-sm font-semibold text-slate-700">Redefinir password</h2>
                                <p className="text-xs text-slate-400 mt-0.5">Definir uma nova password para este utilizador.</p>
                            </div>
                            <button
                                type="button"
                                onClick={() => setShowPasswordForm((v) => !v)}
                                className="rounded-2xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                            >
                                {showPasswordForm ? 'Cancelar' : 'Redefinir'}
                            </button>
                        </div>

                        {showPasswordForm && (
                            <form onSubmit={submitPassword} className="mt-4 space-y-3">
                                <div>
                                    <label className="block text-sm font-medium text-slate-700 mb-1">Nova password *</label>
                                    <input
                                        type="password"
                                        value={data.password}
                                        onChange={(e) => setData('password', e.target.value)}
                                        className="w-full rounded-2xl border border-slate-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        required
                                        autoComplete="new-password"
                                    />
                                    {errors.password && <p className="mt-1 text-xs text-red-600">{errors.password}</p>}
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-slate-700 mb-1">Confirmar password *</label>
                                    <input
                                        type="password"
                                        value={data.password_confirmation}
                                        onChange={(e) => setData('password_confirmation', e.target.value)}
                                        className="w-full rounded-2xl border border-slate-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        required
                                        autoComplete="new-password"
                                    />
                                </div>
                                <div className="flex justify-end">
                                    <button
                                        type="submit"
                                        disabled={processing}
                                        className="rounded-2xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-60"
                                    >
                                        Guardar password
                                    </button>
                                </div>
                            </form>
                        )}
                    </AppCard>
                )}

                {/* Back link */}
                <div>
                    <Link
                        href={route('admin.settings.users.index')}
                        className="text-sm text-blue-600 hover:underline"
                    >
                        ← Voltar à lista
                    </Link>
                </div>
            </div>
        </AdminLayout>
    );
}
