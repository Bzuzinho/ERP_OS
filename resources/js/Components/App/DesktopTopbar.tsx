import type { PageProps } from '@/types';
import { Link, usePage } from '@inertiajs/react';

type DesktopTopbarProps = {
    searchPlaceholder?: string;
};

export default function DesktopTopbar({ searchPlaceholder = 'Pesquisar pedidos, munícipes, documentos...' }: DesktopTopbarProps) {
    const { auth } = usePage<PageProps>().props;

    return (
        <header className="sticky top-0 z-30 hidden border-b border-slate-200/80 bg-white/95 px-8 py-4 backdrop-blur lg:flex lg:items-center lg:justify-between">
            <div className="relative w-full max-w-xl">
                <span className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                    <svg viewBox="0 0 24 24" className="h-4 w-4" fill="none" stroke="currentColor" strokeWidth="1.8" aria-hidden="true">
                        <circle cx="11" cy="11" r="7" />
                        <path d="M20 20L16.5 16.5" />
                    </svg>
                </span>
                <input
                    type="search"
                    placeholder={searchPlaceholder}
                    className="w-full rounded-2xl border border-slate-200 bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                />
            </div>

            <div className="ml-4 flex items-center gap-3">
                <button type="button" className="relative inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-600 transition hover:bg-slate-50">
                    <svg viewBox="0 0 24 24" className="h-5 w-5" fill="none" stroke="currentColor" strokeWidth="1.8" aria-hidden="true">
                        <path d="M15 17h5l-1.5-2v-4a6.5 6.5 0 10-13 0v4L4 17h5" />
                        <path d="M10 19a2 2 0 004 0" />
                    </svg>
                    <span className="absolute right-2 top-2 h-2 w-2 rounded-full bg-blue-600" />
                </button>
                <div className="h-10 w-10 rounded-full border border-slate-200 bg-slate-200" />
                <div className="min-w-0">
                    <p className="truncate text-sm font-semibold text-slate-900">{auth.user?.name}</p>
                    <p className="truncate text-xs text-slate-500">{auth.can.accessAdmin ? 'Administrador' : 'Cidadão'}</p>
                </div>
                <Link href={route('profile.edit')} className="inline-flex items-center gap-1 rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                    Perfil
                    <svg viewBox="0 0 24 24" className="h-4 w-4" fill="none" stroke="currentColor" strokeWidth="1.8" aria-hidden="true">
                        <path d="M9 6l6 6-6 6" />
                    </svg>
                </Link>
            </div>
        </header>
    );
}
