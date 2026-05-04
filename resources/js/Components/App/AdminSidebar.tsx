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
};

export default function AdminSidebar({ items, organizationLabel }: AdminSidebarProps) {
    return (
        <aside className="fixed inset-y-0 left-0 hidden w-[260px] border-r border-slate-200/80 bg-white px-4 py-5 lg:flex lg:flex-col">
            <Link href={route('dashboard')} className="mb-6 inline-flex items-center gap-2 text-slate-900">
                <span className="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-blue-600 text-white">
                    <svg viewBox="0 0 24 24" className="h-5 w-5" fill="none" stroke="currentColor" strokeWidth="1.8" aria-hidden="true">
                        <path d="M3 10h18" />
                        <path d="M5 10l7-5 7 5" />
                        <path d="M6 10v8" />
                        <path d="M10 10v8" />
                        <path d="M14 10v8" />
                        <path d="M18 10v8" />
                        <path d="M4 19h16" />
                    </svg>
                </span>
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
                        <span className="inline-flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 text-blue-700">
                            <svg viewBox="0 0 24 24" className="h-4 w-4" fill="none" stroke="currentColor" strokeWidth="1.8" aria-hidden="true">
                                <path d="M12 3l7 4v5c0 5-3.5 8.5-7 9-3.5-.5-7-4-7-9V7z" />
                            </svg>
                        </span>
                        {organizationLabel}
                    </span>
                    <span aria-hidden="true">›</span>
                </button>
            </div>
        </aside>
    );
}
