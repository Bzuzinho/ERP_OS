import AdminLayout from '@/Layouts/AdminLayout';
import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type Props = {
    serviceArea: {
        id: number;
        name: string;
        slug: string;
        description: string | null;
        is_active: boolean;
    };
};

export default function ServiceAreasEdit({ serviceArea }: Props) {
    const form = useForm({
        name: serviceArea.name,
        slug: serviceArea.slug,
        description: serviceArea.description ?? '',
        is_active: serviceArea.is_active,
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.put(route('admin.settings.service-areas.update', serviceArea.id));
    };

    return (
        <AdminLayout title="Editar area funcional" subtitle={serviceArea.name}>
            <form onSubmit={submit} className="space-y-4 rounded-2xl border border-slate-200 bg-white p-6">
                <div className="grid gap-4 md:grid-cols-2">
                    <input value={form.data.name} onChange={(event) => form.setData('name', event.target.value)} placeholder="Nome" className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                    <input value={form.data.slug} onChange={(event) => form.setData('slug', event.target.value)} placeholder="Slug" className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                    <textarea value={form.data.description} onChange={(event) => form.setData('description', event.target.value)} placeholder="Descricao" className="min-h-24 rounded-xl border border-slate-300 px-3 py-2 text-sm md:col-span-2" />
                    <label className="inline-flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" checked={form.data.is_active} onChange={(event) => form.setData('is_active', event.target.checked)} />
                        Area ativa
                    </label>
                </div>

                <div className="flex items-center gap-3">
                    <button type="submit" className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white">Guardar</button>
                    <Link href={route('admin.settings.service-areas.show', serviceArea.id)} className="text-sm text-slate-700 hover:text-slate-950">Cancelar</Link>
                </div>
            </form>
        </AdminLayout>
    );
}
