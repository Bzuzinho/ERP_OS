import AdminLayout from '@/Layouts/AdminLayout';
import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type DocumentType = { id: number; name: string; slug: string; description?: string | null; is_active: boolean };
type Props = { documentTypes: { data: DocumentType[]; links?: unknown } };

export default function DocumentTypesIndex({ documentTypes }: Props) {
    const deleteForm = useForm({});
    const handleDelete = (id: number) => {
        if (!confirm('Eliminar este tipo de documento?')) return;
        deleteForm.delete(route('admin.document-types.destroy', id));
    };

    return (
        <AdminLayout
            title="Tipos de Documento"
            subtitle="Categorias dos documentos"
            headerActions={
                <Link href={route('admin.document-types.create')} className="inline-flex rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                    Criar Tipo
                </Link>
            }
        >
            <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                <table className="min-w-full divide-y divide-slate-200 text-sm">
                    <thead className="bg-slate-50 text-left text-slate-600">
                        <tr>
                            <th className="px-4 py-3">Nome</th>
                            <th className="px-4 py-3">Slug</th>
                            <th className="px-4 py-3">Descrição</th>
                            <th className="px-4 py-3">Ativo</th>
                            <th className="px-4 py-3">Ações</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                        {documentTypes.data.map((dt) => (
                            <tr key={dt.id}>
                                <td className="px-4 py-3 font-medium text-slate-900">{dt.name}</td>
                                <td className="px-4 py-3 text-slate-600"><code className="rounded bg-slate-100 px-1 text-xs">{dt.slug}</code></td>
                                <td className="px-4 py-3 text-slate-600">{dt.description ?? '-'}</td>
                                <td className="px-4 py-3">
                                    <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ${dt.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'}`}>
                                        {dt.is_active ? 'Sim' : 'Não'}
                                    </span>
                                </td>
                                <td className="px-4 py-3 flex gap-3 text-xs">
                                    <Link href={route('admin.document-types.edit', dt.id)} className="text-slate-700 hover:text-slate-900">Editar</Link>
                                    <button onClick={() => handleDelete(dt.id)} className="text-rose-600 hover:text-rose-800">Eliminar</button>
                                </td>
                            </tr>
                        ))}
                        {documentTypes.data.length === 0 ? (
                            <tr><td colSpan={5} className="px-4 py-6 text-center text-slate-500">Sem tipos de documento.</td></tr>
                        ) : null}
                    </tbody>
                </table>
            </div>
        </AdminLayout>
    );
}
