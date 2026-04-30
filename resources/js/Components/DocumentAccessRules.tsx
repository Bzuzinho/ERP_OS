import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type Rule = {
    id: number;
    user_id?: number | null;
    contact_id?: number | null;
    role_name?: string | null;
    permission: string;
    expires_at?: string | null;
    user?: { id: number; name: string } | null;
    contact?: { id: number; name: string } | null;
};

type Props = {
    documentId: number;
    rules: Rule[];
    canManage: boolean;
};

export default function DocumentAccessRules({ documentId, rules, canManage }: Props) {
    const form = useForm({ user_id: '', contact_id: '', role_name: '', permission: 'view', expires_at: '' });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(route('admin.documents.access-rules.store', documentId));
    };

    return (
        <section className="rounded-2xl border border-slate-200 bg-white p-4">
            <h3 className="text-lg font-semibold text-slate-900">Regras de acesso</h3>

            {canManage ? (
                <form onSubmit={submit} className="mt-3 grid gap-2 md:grid-cols-5">
                    <input placeholder="User ID" value={form.data.user_id} onChange={(event) => form.setData('user_id', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                    <input placeholder="Contact ID" value={form.data.contact_id} onChange={(event) => form.setData('contact_id', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                    <input placeholder="Role" value={form.data.role_name} onChange={(event) => form.setData('role_name', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                    <select value={form.data.permission} onChange={(event) => form.setData('permission', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                        <option value="view">view</option>
                        <option value="download">download</option>
                        <option value="manage">manage</option>
                    </select>
                    <button type="submit" className="rounded-xl bg-slate-900 px-3 py-2 text-sm font-medium text-white hover:bg-slate-700">Adicionar</button>
                </form>
            ) : null}

            <ul className="mt-3 space-y-2 text-sm">
                {rules.map((rule) => (
                    <li key={rule.id} className="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <div>
                            <p className="font-medium text-slate-900">
                                {rule.user?.name ?? rule.contact?.name ?? rule.role_name ?? 'Regra'} - {rule.permission}
                            </p>
                            <p className="text-xs text-slate-600">Expira: {rule.expires_at ? new Date(rule.expires_at).toLocaleString() : 'Sem expiracao'}</p>
                        </div>
                        {canManage ? (
                            <Link
                                href={route('admin.documents.access-rules.destroy', { document: documentId, documentAccessRule: rule.id })}
                                method="delete"
                                as="button"
                                className="rounded-lg bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700"
                            >
                                Remover
                            </Link>
                        ) : null}
                    </li>
                ))}
            </ul>
        </section>
    );
}
