import { useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type AttachmentUploaderProps = {
    storeRoute: string;
    canSetVisibility?: boolean;
};

export default function AttachmentUploader({ storeRoute, canSetVisibility = false }: AttachmentUploaderProps) {
    const form = useForm<{ file: File | null; visibility: string }>({
        file: null,
        visibility: 'internal',
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(storeRoute, {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => form.reset('file'),
        });
    };

    return (
        <form onSubmit={submit} className="rounded-2xl border border-slate-200 bg-white p-4">
            <h3 className="text-lg font-semibold text-slate-900">Enviar anexo</h3>

            <input
                type="file"
                onChange={(event) => form.setData('file', event.target.files?.[0] ?? null)}
                className="mt-3 block w-full text-sm"
            />

            <div className="mt-3 flex items-center gap-3">
                {canSetVisibility ? (
                    <select
                        value={form.data.visibility}
                        onChange={(event) => form.setData('visibility', event.target.value)}
                        className="rounded-xl border border-slate-300 px-3 py-2 text-sm"
                    >
                        <option value="internal">Interno</option>
                        <option value="public">Publico</option>
                    </select>
                ) : null}

                <button type="submit" disabled={form.processing || !form.data.file} className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 disabled:opacity-50">
                    Enviar
                </button>
            </div>
        </form>
    );
}
