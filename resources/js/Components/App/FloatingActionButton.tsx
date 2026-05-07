import { Link } from '@inertiajs/react';

type FloatingActionButtonProps = {
    href: string;
    label: string;
};

export default function FloatingActionButton({ href, label }: FloatingActionButtonProps) {
    return (
        <Link
            href={href}
            aria-label={label}
            className="fixed bottom-24 right-4 z-30 inline-flex min-h-14 items-center justify-center gap-2 rounded-2xl bg-blue-600 px-4 text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700 active:bg-blue-800 lg:hidden"
        >
            <svg viewBox="0 0 24 24" className="h-6 w-6" fill="none" stroke="currentColor" strokeWidth="2.2" aria-hidden="true">
                <path d="M12 5v14" />
                <path d="M5 12h14" />
            </svg>
            <span className="text-sm font-semibold">{label}</span>
        </Link>
    );
}
