import { useState } from 'react';

type Attachment = {
    id: number;
    file_name: string;
    file_path: string;
    visibility?: string;
};

type AttachmentListProps = {
    attachments: Attachment[];
    downloadRouteName: string;
};

const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'];

function isImage(fileName: string): boolean {
    const ext = fileName.split('.').pop()?.toLowerCase() ?? '';
    return IMAGE_EXTENSIONS.includes(ext);
}

export default function AttachmentList({ attachments, downloadRouteName }: AttachmentListProps) {
    const [lightboxUrl, setLightboxUrl] = useState<string | null>(null);
    const [lightboxName, setLightboxName] = useState<string>('');

    const previewUrl = (attachment: Attachment) =>
        `${route(downloadRouteName, attachment.id)}?inline=1`;

    const downloadUrl = (attachment: Attachment) =>
        route(downloadRouteName, attachment.id);

    const openLightbox = (attachment: Attachment) => {
        setLightboxUrl(previewUrl(attachment));
        setLightboxName(attachment.file_name);
    };

    const closeLightbox = () => setLightboxUrl(null);

    return (
        <>
            <ul className="mt-3 space-y-2 text-sm">
                {attachments.map((attachment) =>
                    isImage(attachment.file_name) ? (
                        <li key={attachment.id} className="rounded-2xl border border-slate-100 bg-slate-50 p-3.5">
                            <button
                                type="button"
                                onClick={() => openLightbox(attachment)}
                                className="font-medium text-blue-700 hover:text-blue-900 hover:underline text-left"
                            >
                                {attachment.file_name}
                            </button>
                            {attachment.visibility ? (
                                <p className="mt-1 text-xs text-slate-600">{attachment.visibility}</p>
                            ) : null}
                        </li>
                    ) : (
                        <li key={attachment.id} className="rounded-2xl border border-slate-100 bg-slate-50 p-3.5">
                            <a
                                href={downloadUrl(attachment)}
                                className="font-medium text-slate-900 hover:text-slate-700"
                            >
                                {attachment.file_name}
                            </a>
                            {attachment.visibility ? (
                                <p className="mt-1 text-xs text-slate-600">{attachment.visibility}</p>
                            ) : null}
                        </li>
                    ),
                )}
            </ul>

            {lightboxUrl ? (
                <div
                    role="dialog"
                    aria-modal="true"
                    aria-label={lightboxName}
                    className="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4"
                    onClick={closeLightbox}
                >
                    <div
                        className="relative max-h-full max-w-4xl"
                        onClick={(e) => e.stopPropagation()}
                    >
                        <button
                            type="button"
                            onClick={closeLightbox}
                            aria-label="Fechar"
                            className="absolute -top-3 -right-3 z-10 flex h-8 w-8 items-center justify-center rounded-full bg-white shadow-md text-slate-700 hover:bg-slate-100"
                        >
                            ✕
                        </button>
                        <img
                            src={lightboxUrl}
                            alt={lightboxName}
                            className="max-h-[80vh] max-w-full rounded-2xl object-contain shadow-2xl"
                        />
                        <p className="mt-2 text-center text-sm text-white/80">{lightboxName}</p>
                    </div>
                </div>
            ) : null}
        </>
    );
}
