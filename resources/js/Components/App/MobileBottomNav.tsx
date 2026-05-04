import { Link } from '@inertiajs/react';
import type { ReactNode } from 'react';

type MobileBottomNavItem = {
    label: string;
    href: string;
    activePatterns: string[];
    icon: ReactNode;
};

type MobileBottomNavProps = {
    items: MobileBottomNavItem[];
};

export default function MobileBottomNav({ items }: MobileBottomNavProps) {
    return (
        <nav className="fixed bottom-0 left-0 right-0 z-40 border-t border-slate-200 bg-white px-2 pb-[max(env(safe-area-inset-bottom),8px)] pt-2 lg:hidden">
            <ul className="grid grid-cols-5 gap-1">
                {items.map((item) => {
                    const isActive = item.activePatterns.some((pattern) => route().current(pattern));

                    return (
                        <li key={item.label}>
                            <Link
                                href={item.href}
                                className={
                                    isActive
                                        ? 'flex flex-col items-center gap-1 rounded-2xl px-1 py-2 text-blue-600'
                                        : 'flex flex-col items-center gap-1 rounded-2xl px-1 py-2 text-slate-500 hover:bg-slate-50'
                                }
                            >
                                <span className="h-5 w-5">{item.icon}</span>
                                <span className="text-[11px] font-semibold">{item.label}</span>
                            </Link>
                        </li>
                    );
                })}
            </ul>
        </nav>
    );
}
