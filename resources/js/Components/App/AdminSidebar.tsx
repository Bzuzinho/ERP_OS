import type { ReactNode } from 'react';
import { Link } from '@inertiajs/react';

type SidebarItem = {
    label: string;
    href: string;
    patterns: string[];
    icon: ReactNode;
};

type AdminSidebarProps = {
    items: SidebarItem[];
    organizationLabel: string;
    organizationLogoUrl?: string | null;
};

export default function AdminSidebar({ items, organizationLabel, organizationLogoUrl }: AdminSidebarProps) {
    return (
        <aside className="fixed inset-y-0 left-0 hidden w-[260px] border-r border-slate-200/80 bg-white px-4 py-5 lg:flex lg:flex-col">
            <Link href={route('dashboard')} className="mb-6 inline-flex items-center gap-2 text-slate-900">
                <img src="/branding/logo.png" alt="JuntaOS" className="h-10 w-10 object-contain" />
                <span className="text-[32px] font-bold tracking-tight text-blue-600">JuntaOS</span>
            </Link>

            <nav className="space-y-1">
                {items.map((item) => {
                    const isActive = item.patterns.some((pattern) => route().current(pattern));

                    return (
                        <Link
                            key={item.label}
                            href={item.href}
                            className={
                                isActive
                                    ? 'flex items-center gap-3 rounded-2xl bg-blue-50 px-3 py-2.5 text-sm font-semibold text-blue-700'
                                    : 'flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-100'
                            }
                        >
                            <span className="h-5 w-5">{item.icon}</span>
                            <span>{item.label}</span>
                        </Link>
                    );
                })}
            </nav>

            <div className="mt-auto rounded-2xl border border-slate-200 bg-slate-50 p-3">
                <p className="text-xs font-medium text-slate-500">Organização</p>
                <button type="button" className="mt-2 flex w-full items-center justify-between text-left text-sm font-semibold text-slate-800">
                    <span className="inline-flex items-center gap-2 truncate">
                        {organizationLogoUrl ? (
                            <img src={organizationLogoUrl} alt="" className="h-6 w-6 rounded-full object-contain" />
                        ) : (
                            <span className="inline-flex h-6 w-6 items-center justify-center rounded-full bg-slate-200 text-xs font-bold text-slate-600">
                                {organizationLabel.charAt(0).toUpperCase()}
                            </span>
                        )}
                        {organizationLabel}
                    </span>
                    <span aria-hidden="true">›</span>
                </button>
            </div>
        </aside>
    );
}
