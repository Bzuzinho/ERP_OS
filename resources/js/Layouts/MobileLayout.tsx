import type { PageProps } from '@/types';
import { Head, usePage } from '@inertiajs/react';
import { type PropsWithChildren, type ReactNode } from 'react';

type MobileLayoutProps = PropsWithChildren<{
    title: string;
    subtitle?: string;
    headerActions?: ReactNode;
}>;

const iconToday = (
    <svg viewBox="0 0 24 24" className="h-5 w-5" fill="none" stroke="currentColor" strokeWidth="1.8" aria-hidden="true">
        <rect x="3" y="5" width="18" height="16" rx="2" />
        <path d="M8 3v4" />
        <path d="M16 3v4" />
        <path d="M3 10h18" />
    </svg>
);

const iconTasks = (
    <svg viewBox="0 0 24 24" className="h-5 w-5" fill="none" stroke="currentColor" strokeWidth="1.8" aria-hidden="true">
        <path d="M5 12h14" />
        <path d="M5 6h14" />
        <path d="M5 18h14" />
    </svg>
);

const iconCalendar = (
    <svg viewBox="0 0 24 24" className="h-5 w-5" fill="none" stroke="currentColor" strokeWidth="1.8" aria-hidden="true">
        <rect x="3" y="5" width="18" height="16" rx="2" />
        <path d="M8 3v4" />
        <path d="M16 3v4" />
        <path d="M3 10h18" />
    </svg>
);

const iconMessages = (
    <svg viewBox="0 0 24 24" className="h-5 w-5" fill="none" stroke="currentColor" strokeWidth="1.8" aria-hidden="true">
        <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z" />
    </svg>
);

const iconMore = (
    <svg viewBox="0 0 24 24" className="h-5 w-5" fill="none" stroke="currentColor" strokeWidth="1.8" aria-hidden="true">
        <circle cx="12" cy="5" r="1" />
        <circle cx="12" cy="12" r="1" />
        <circle cx="12" cy="19" r="1" />
    </svg>
);

const navigationItems = [
    { label: 'Hoje', href: route('mobile.today.index'), activePatterns: ['mobile.today.*'], icon: iconToday },
    { label: 'Tarefas', href: route('mobile.tasks.index'), activePatterns: ['mobile.tasks.*'], icon: iconTasks },
    { label: 'Agenda', href: route('mobile.calendar.index'), activePatterns: ['mobile.calendar.*'], icon: iconCalendar },
    { label: 'Comunicações', href: route('mobile.communications.index'), activePatterns: ['mobile.communications.*'], icon: iconMessages },
    { label: 'Mais', href: route('mobile.more.index'), activePatterns: ['mobile.more.*'], icon: iconMore },
];

function isActiveRoute(routeName: string, patterns: string[]): boolean {
    return patterns.some(pattern => {
        if (pattern.endsWith('.*')) {
            const prefix = pattern.slice(0, -2);
            return routeName.startsWith(prefix);
        }
        return routeName === pattern;
    });
}

export default function MobileLayout({
    children,
    title,
    subtitle,
    headerActions,
}: MobileLayoutProps) {
    const page = usePage<PageProps>();

    return (
        <>
            <Head title={title} />

            <div className="flex flex-col h-screen bg-gray-50">
                {/* Header */}
                <div className="sticky top-0 z-40 bg-white border-b border-gray-200">
                    <div className="px-4 py-3 sm:px-6">
                        <div className="flex items-center justify-between">
                            <div>
                                <h1 className="text-xl font-semibold text-gray-900">{title}</h1>
                                {subtitle && <p className="text-sm text-gray-500 mt-1">{subtitle}</p>}
                            </div>
                            {headerActions && <div>{headerActions}</div>}
                        </div>
                    </div>
                </div>

                {/* Main Content */}
                <div className="flex-1 overflow-y-auto pb-20 md:pb-4">
                    <div className="w-full max-w-2xl mx-auto">{children}</div>
                </div>

                {/* Bottom Navigation */}
                <nav className="fixed bottom-0 left-0 right-0 md:relative bg-white border-t border-gray-200">
                    <div className="flex justify-around items-stretch max-w-2xl mx-auto">
                        {navigationItems.map(item => (
                            <a
                                key={item.href}
                                href={item.href}
                                className={`flex-1 flex flex-col items-center justify-center py-2 px-4 transition-colors ${
                                    isActiveRoute(page.component, item.activePatterns)
                                        ? 'text-blue-600 border-t-2 border-blue-600'
                                        : 'text-gray-600 hover:text-gray-900'
                                }`}
                            >
                                <div className="h-6 w-6 mb-1">{item.icon}</div>
                                <span className="text-xs font-medium text-center truncate">{item.label}</span>
                            </a>
                        ))}
                    </div>
                </nav>
            </div>
        </>
    );
}
