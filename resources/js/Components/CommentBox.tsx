import { useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type CommentBoxProps = {
    storeRoute: string;
    canSetVisibility?: boolean;
    defaultVisibility?: 'internal' | 'public';
    title?: string;
    helperText?: string;
    submitLabel?: string;
};

export default function CommentBox({
    storeRoute,
    canSetVisibility = false,
    defaultVisibility = 'internal',
    title = 'Novo comentario',
    helperText,
    submitLabel = 'Comentar',
}: CommentBoxProps) {
    const form = useForm({
        body: '',
        visibility: defaultVisibility,
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(storeRoute, {
            preserveScroll: true,
            onSuccess: () => form.reset('body'),
        });
    };

    return (
        <form onSubmit={submit} className="rounded-2xl border border-slate-200 bg-white p-4">
            <h3 className="text-lg font-semibold text-slate-900">{title}</h3>
            {helperText ? <p className="mt-1 text-xs text-slate-600">{helperText}</p> : null}
            <textarea
                value={form.data.body}
                onChange={(event) => form.setData('body', event.target.value)}
                className="mt-3 min-h-28 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm"
                placeholder="Escreva o comentario"
            />

            <div className="mt-3 flex items-center gap-3">
                {canSetVisibility ? (
                    <select
                        value={form.data.visibility}
                        onChange={(event) => form.setData('visibility', event.target.value as 'internal' | 'public')}
                        className="rounded-xl border border-slate-300 px-3 py-2 text-sm"
                    >
                        <option value="internal">Interno</option>
                        <option value="public">Publico</option>
                    </select>
                ) : null}

                <button type="submit" disabled={form.processing} className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 disabled:opacity-50">
                    {submitLabel}
                </button>
            </div>
        </form>
    );
}
