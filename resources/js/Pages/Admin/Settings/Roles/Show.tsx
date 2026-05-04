import AppBadge from '@/Components/App/AppBadge';
import AppCard from '@/Components/App/AppCard';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link } from '@inertiajs/react';

type Permission = { id: number; name: string };
type RoleDetail = { id: number; name: string; permissions: Permission[] };

type Props = {
    role: RoleDetail;
    allPermissions: Permission[];
    canEdit: boolean;
};

function groupPermissions(permissions: Permission[]): Record<string, Permission[]> {
    const grouped: Record<string, Permission[]> = {};
    for (const p of permissions) {
        const dot = p.name.indexOf('.');
        const module = dot !== -1 ? p.name.substring(0, dot) : p.name;
        if (!grouped[module]) grouped[module] = [];
        grouped[module].push(p);
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

export default function RolesShow({ role, canEdit }: Props) {
    const grouped = groupPermissions(role.permissions);

    return (
        <AdminLayout
            title={role.name}
            subtitle="Permissões do perfil"
            headerActions={
                canEdit ? (
                    <Link
                        href={route('admin.settings.roles.edit', role.id)}
                        className="rounded-2xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
                    >
                        Editar permissões
                    </Link>
                ) : undefined
            }
        >
            <Head title={role.name} />

            <div className="max-w-3xl space-y-5">
                <AppCard>
                    <div className="flex items-center gap-3">
                        <AppBadge tone={roleTone(role.name)}>{role.name}</AppBadge>
                        <span className="text-sm text-slate-500">{role.permissions.length} permissões</span>
                    </div>
                </AppCard>

                <AppCard>
                    <h2 className="mb-4 text-sm font-semibold text-slate-700 uppercase tracking-wide">Permissões por módulo</h2>
                    {role.permissions.length === 0 ? (
                        <p className="text-sm text-slate-400">Sem permissões atribuídas.</p>
                    ) : (
                        <div className="space-y-4">
                            {Object.entries(grouped)
                                .sort(([a], [b]) => a.localeCompare(b))
                                .map(([module, perms]) => (
                                    <div key={module}>
                                        <p className="mb-2 text-xs font-semibold text-slate-500 uppercase tracking-wide">{module}</p>
                                        <div className="flex flex-wrap gap-1.5">
                                            {perms.map((p) => (
                                                <span key={p.id} className="rounded-full bg-slate-100 px-2.5 py-1 text-xs text-slate-600">
                                                    {p.name}
                                                </span>
                                            ))}
                                        </div>
                                    </div>
                                ))}
                        </div>
                    )}
                </AppCard>

                <Link href={route('admin.settings.roles.index')} className="text-sm text-blue-600 hover:underline">
                    ← Voltar aos perfis
                </Link>
            </div>
        </AdminLayout>
    );
}
