import AppCard from '@/Components/App/AppCard';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, useForm } from '@inertiajs/react';

type Permission = { id: number; name: string };
type RoleDetail = { id: number; name: string; permissions: Permission[] };

type Props = {
    role: RoleDetail;
    allPermissions: Permission[];
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

export default function RolesEdit({ role, allPermissions }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        permissions: role.permissions.map((p) => p.name),
    });

    const togglePerm = (name: string) => {
        setData('permissions', data.permissions.includes(name)
            ? data.permissions.filter((p) => p !== name)
            : [...data.permissions, name],
        );
    };

    const toggleModule = (perms: Permission[]) => {
        const moduleNames = perms.map((p) => p.name);
        const allSelected = moduleNames.every((n) => data.permissions.includes(n));
        if (allSelected) {
            setData('permissions', data.permissions.filter((p) => !moduleNames.includes(p)));
        } else {
            const newSet = new Set([...data.permissions, ...moduleNames]);
            setData('permissions', Array.from(newSet));
        }
    };

    const grouped = groupPermissions(allPermissions);

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        put(route('admin.settings.roles.update', role.id));
    };

    return (
        <AdminLayout title={`Editar: ${role.name}`} subtitle="Definir permissões do perfil">
            <Head title={`Editar: ${role.name}`} />

            <div className="max-w-3xl">
                <form onSubmit={submit} className="space-y-5">
                    {Object.entries(grouped)
                        .sort(([a], [b]) => a.localeCompare(b))
                        .map(([module, perms]) => {
                            const allSelected = perms.every((p) => data.permissions.includes(p.name));
                            const someSelected = perms.some((p) => data.permissions.includes(p.name));
                            return (
                                <AppCard key={module}>
                                    <div className="mb-3 flex items-center justify-between">
                                        <p className="text-sm font-semibold text-slate-700 uppercase tracking-wide">{module}</p>
                                        <button
                                            type="button"
                                            onClick={() => toggleModule(perms)}
                                            className={`text-xs font-semibold ${allSelected ? 'text-red-500' : 'text-blue-600'} hover:underline`}
                                        >
                                            {allSelected ? 'Remover todas' : 'Selecionar todas'}
                                        </button>
                                    </div>
                                    <div className="flex flex-wrap gap-2">
                                        {perms.map((p) => {
                                            const selected = data.permissions.includes(p.name);
                                            return (
                                                <button
                                                    key={p.id}
                                                    type="button"
                                                    onClick={() => togglePerm(p.name)}
                                                    className={`rounded-2xl border px-3 py-1.5 text-xs font-medium transition-colors ${
                                                        selected
                                                            ? 'border-blue-500 bg-blue-50 text-blue-700'
                                                            : 'border-slate-200 bg-white text-slate-500 hover:border-slate-300'
                                                    }`}
                                                >
                                                    {p.name}
                                                </button>
                                            );
                                        })}
                                    </div>
                                </AppCard>
                            );
                        })}

                    {errors.permissions && (
                        <p className="text-sm text-red-600">{errors.permissions}</p>
                    )}

                    <div className="flex items-center justify-between">
                        <Link
                            href={route('admin.settings.roles.show', role.id)}
                            className="text-sm text-blue-600 hover:underline"
                        >
                            ← Cancelar
                        </Link>
                        <button
                            type="submit"
                            disabled={processing}
                            className="rounded-2xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-60"
                        >
                            Guardar permissões
                        </button>
                    </div>
                </form>
            </div>
        </AdminLayout>
    );
}
