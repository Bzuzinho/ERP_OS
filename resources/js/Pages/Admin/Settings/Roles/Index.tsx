import AppBadge from '@/Components/App/AppBadge';
import AppCard from '@/Components/App/AppCard';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link } from '@inertiajs/react';

type RoleRow = {
    id: number;
    name: string;
    users_count: number;
    permissions_count: number;
};

type Props = {
    roles: RoleRow[];
};

const roleTone = (name: string): 'blue' | 'indigo' | 'amber' | 'green' | 'slate' | 'red' => {
    if (name === 'super_admin') return 'red';
    if (name === 'admin_junta') return 'indigo';
    if (name === 'executivo') return 'blue';
    if (name === 'rh') return 'green';
    if (['cidadao', 'associacao', 'empresa'].includes(name)) return 'amber';
    return 'slate';
};

export default function RolesIndex({ roles }: Props) {
    return (
        <AdminLayout
            title="Perfis e Permissões"
            subtitle="Gerir os perfis de acesso da plataforma"
        >
            <Head title="Perfis e Permissões" />

            <div className="max-w-3xl space-y-3">
                {roles.map((role) => (
                    <AppCard key={role.id} className="flex items-center justify-between gap-4">
                        <div className="flex items-center gap-3 min-w-0">
                            <AppBadge tone={roleTone(role.name)}>{role.name}</AppBadge>
                            <div className="flex gap-4 text-xs text-slate-500 ml-2">
                                <span>{role.users_count} utilizador{role.users_count !== 1 ? 'es' : ''}</span>
                                <span>{role.permissions_count} permiss{role.permissions_count !== 1 ? 'ões' : 'ão'}</span>
                            </div>
                        </div>
                        <div className="flex gap-2 shrink-0">
                            <Link
                                href={route('admin.settings.roles.show', role.id)}
                                className="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                            >
                                Ver
                            </Link>
                            <Link
                                href={route('admin.settings.roles.edit', role.id)}
                                className="rounded-xl border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 hover:bg-blue-100"
                            >
                                Editar
                            </Link>
                        </div>
                    </AppCard>
                ))}
            </div>
        </AdminLayout>
    );
}
