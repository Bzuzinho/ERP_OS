import type { PageProps } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import type { PropsWithChildren, ReactNode } from 'react';

type AdminLayoutProps = PropsWithChildren<{
    title: string;
    subtitle: string;
    headerActions?: ReactNode;
}>;

const navigationItems = [
    { label: 'Dashboard', href: '/admin', current: 'admin.dashboard', enabled: true },
    { label: 'Utilizadores', href: '#', current: '', enabled: false },
    { label: 'Contactos', href: '#', current: '', enabled: false },
    { label: 'Pedidos', href: '#', current: '', enabled: false },
    { label: 'Agenda', href: '#', current: '', enabled: false },
    { label: 'Espaços', href: '#', current: '', enabled: false },
    { label: 'Inventário', href: '#', current: '', enabled: false },
    { label: 'RH', href: '#', current: '', enabled: false },
    { label: 'Planeamento', href: '#', current: '', enabled: false },
    { label: 'Relatórios', href: '#', current: '', enabled: false },
    { label: 'Configurações', href: '#', current: '', enabled: false },
];

export default function AdminLayout({
    children,
    title,
    subtitle,
    headerActions,
}: AdminLayoutProps) {
    const { auth } = usePage<PageProps>().props;
    const user = auth.user;

    return (
        <div className="min-h-screen bg-slate-100 text-slate-900">
            <Head title={title} />

            <div className="border-b border-slate-200 bg-white">
                <div className="mx-auto flex max-w-7xl flex-col gap-6 px-4 py-5 sm:px-6 lg:px-8">
                    <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <Link href="/admin" className="text-sm font-semibold uppercase tracking-[0.24em] text-emerald-700">
                                JuntaOS Admin
                            </Link>
                            <h1 className="mt-2 text-3xl font-semibold text-slate-950">{title}</h1>
                            <p className="mt-2 max-w-2xl text-sm text-slate-600">{subtitle}</p>
                        </div>

                        <div className="flex flex-col items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600 shadow-sm lg:items-end">
                            <div className="font-medium text-slate-900">{user?.name}</div>
                            <div>{user?.organization?.name ?? 'Sem organização atribuída'}</div>
                            <div className="flex gap-3">
                                <Link href="/portal" className="font-medium text-slate-700 transition hover:text-slate-950">
                                    Portal
                                </Link>
                                <Link href={route('profile.edit')} className="font-medium text-slate-700 transition hover:text-slate-950">
                                    Perfil
                                </Link>
                                <Link href={route('logout')} method="post" as="button" className="font-medium text-rose-700 transition hover:text-rose-900">
                                    Terminar sessão
                                </Link>
                            </div>
                        </div>
                    </div>

                    <nav className="flex flex-wrap gap-2">
                        {navigationItems.map((item) => {
                            const isCurrent = item.current ? route().current(item.current) : false;

                            if (!item.enabled) {
                                return (
                                    <span
                                        key={item.label}
                                        className="rounded-full border border-dashed border-slate-300 px-4 py-2 text-sm text-slate-400"
                                    >
                                        {item.label}
                                    </span>
                                );
                            }

                            return (
                                <Link
                                    key={item.label}
                                    href={item.href}
                                    className={
                                        isCurrent
                                            ? 'rounded-full bg-slate-900 px-4 py-2 text-sm font-medium text-white'
                                            : 'rounded-full border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:text-slate-950'
                                    }
                                >
                                    {item.label}
                                </Link>
                            );
                        })}
                    </nav>
                </div>
            </div>

            <main className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                {headerActions ? <div className="mb-6">{headerActions}</div> : null}
                {children}
            </main>
        </div>
    );
}