import { Link, usePage } from '@inertiajs/react';
import type { PageProps } from '@/types';
import NotificationBell from '@/Components/App/NotificationBell';

type MobileHeaderProps = {
    title: string;
    subtitle?: string;
    showBack?: boolean;
    backHref?: string;
};

export default function MobileHeader({ title, subtitle, showBack = false, backHref = '#' }: MobileHeaderProps) {
    const { auth } = usePage<PageProps>().props;
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
                        <img src="/branding/logo.png" alt="JuntaOS" className="h-9 w-9 object-contain" />
                    )}
                    <div className="min-w-0">
                        <p className={showBack ? 'truncate text-base font-bold text-slate-900' : 'truncate text-3xl font-bold leading-none text-blue-600'}>
                            {showBack ? title : 'JuntaOS'}
                        </p>
                        {subtitle ? <p className="truncate text-xs text-slate-500">{subtitle}</p> : null}
                    </div>
                </div>
                <div className="flex items-center gap-2">
                    <NotificationBell compact />
                    <div className="h-9 w-9 shrink-0 overflow-hidden rounded-full border border-slate-200 bg-slate-200">
                        {auth.user?.avatar_path ? (
                            <img src={`/storage/${auth.user.avatar_path}`} alt="" className="h-full w-full object-cover" />
                        ) : (
                            <span className="flex h-full w-full items-center justify-center text-xs font-bold text-slate-500">
                                {auth.user?.name?.charAt(0).toUpperCase()}
                            </span>
                        )}
                    </div>
                </div>
            </div>
            {showBack ? null : <h1 className="mt-3 text-xl font-bold text-slate-950">{title}</h1>}
        </header>
    );
}
