import type { PageProps } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import type { PropsWithChildren, ReactNode } from 'react';

type PortalLayoutProps = PropsWithChildren<{
    title: string;
    subtitle: string;
    headerActions?: ReactNode;
}>;

const navigationItems = [
    { label: 'O meu portal', href: '/portal', current: 'portal.dashboard', enabled: true },
    { label: 'Pedidos', href: route('portal.tickets.index'), current: 'portal.tickets.*', enabled: true },
    { label: 'Reservas', href: '#', current: '', enabled: false },
    { label: 'Documentos', href: '#', current: '', enabled: false },
    { label: 'Notificações', href: '#', current: '', enabled: false },
    { label: 'Perfil', href: route('profile.edit'), current: 'profile.edit', enabled: true },
];

export default function PortalLayout({
    children,
    title,
    subtitle,
    headerActions,
}: PortalLayoutProps) {
    const { auth } = usePage<PageProps>().props;
    const user = auth.user;

    return (
        <div className="min-h-screen bg-stone-50 text-stone-900">
            <Head title={title} />

            <div className="border-b border-stone-200 bg-white/95 backdrop-blur">
                <div className="mx-auto flex max-w-6xl flex-col gap-6 px-4 py-5 sm:px-6 lg:px-8">
                    <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <Link href="/portal" className="text-sm font-semibold uppercase tracking-[0.24em] text-amber-700">
                                JuntaOS Portal
                            </Link>
                            <h1 className="mt-2 text-3xl font-semibold text-stone-950">{title}</h1>
                            <p className="mt-2 max-w-2xl text-sm text-stone-600">{subtitle}</p>
                        </div>

                        <div className="flex flex-col items-start gap-3 rounded-2xl border border-stone-200 bg-stone-100 px-4 py-3 text-sm text-stone-600 shadow-sm lg:items-end">
                            <div className="font-medium text-stone-900">{user?.name}</div>
                            <div>{user?.email}</div>
                            <div className="flex gap-3">
                                {auth.can.accessAdmin ? (
                                    <Link href="/admin" className="font-medium text-stone-700 transition hover:text-stone-950">
                                        Administração
                                    </Link>
                                ) : null}
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
                                        className="rounded-full border border-dashed border-stone-300 px-4 py-2 text-sm text-stone-400"
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
                                            ? 'rounded-full bg-amber-600 px-4 py-2 text-sm font-medium text-white'
                                            : 'rounded-full border border-stone-300 px-4 py-2 text-sm font-medium text-stone-700 transition hover:border-stone-400 hover:text-stone-950'
                                    }
                                >
                                    {item.label}
                                </Link>
                            );
                        })}
                    </nav>
                </div>
            </div>

            <main className="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
                {headerActions ? <div className="mb-6">{headerActions}</div> : null}
                {children}
            </main>
        </div>
    );
}