import { Link } from '@inertiajs/react';

type MobileHeaderProps = {
    title: string;
    subtitle?: string;
    showBack?: boolean;
    backHref?: string;
};

export default function MobileHeader({ title, subtitle, showBack = false, backHref = '#' }: MobileHeaderProps) {
    return (
        <header className="sticky top-0 z-40 border-b border-slate-200 bg-white/95 px-4 pb-3 pt-3 backdrop-blur lg:hidden">
            <div className="flex items-center justify-between gap-3">
                <div className="flex min-w-0 items-center gap-2">
                    {showBack ? (
                        <Link href={backHref} className="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 text-slate-600">
                            <svg viewBox="0 0 24 24" className="h-5 w-5" fill="none" stroke="currentColor" strokeWidth="1.8" aria-hidden="true">
                                <path d="M15 18l-6-6 6-6" />
                            </svg>
                        </Link>
                    ) : (
                        <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-600 text-white">
                            <svg viewBox="0 0 24 24" className="h-5 w-5" fill="none" stroke="currentColor" strokeWidth="1.8" aria-hidden="true">
                                <path d="M3 10h18" />
                                <path d="M5 10l7-5 7 5" />
                                <path d="M6 10v8" />
                                <path d="M10 10v8" />
                                <path d="M14 10v8" />
                                <path d="M18 10v8" />
                                <path d="M4 19h16" />
                            </svg>
                        </div>
                    )}
                    <div className="min-w-0">
                        <p className={showBack ? 'truncate text-base font-bold text-slate-900' : 'truncate text-3xl font-bold leading-none text-blue-600'}>
                            {showBack ? title : 'JuntaOS'}
                        </p>
                        {subtitle ? <p className="truncate text-xs text-slate-500">{subtitle}</p> : null}
                    </div>
                </div>
                <div className="flex items-center gap-2">
                    <button type="button" className="relative inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 text-slate-600">
                        <svg viewBox="0 0 24 24" className="h-5 w-5" fill="none" stroke="currentColor" strokeWidth="1.8" aria-hidden="true">
                            <path d="M15 17h5l-1.5-2v-4a6.5 6.5 0 10-13 0v4L4 17h5" />
                            <path d="M10 19a2 2 0 004 0" />
                        </svg>
                        <span className="absolute right-1 top-1 h-2 w-2 rounded-full bg-blue-600" />
                    </button>
                    <div className="h-9 w-9 rounded-full bg-slate-200" />
                </div>
            </div>
            {showBack ? null : <h1 className="mt-3 text-xl font-bold text-slate-950">{title}</h1>}
        </header>
    );
}
