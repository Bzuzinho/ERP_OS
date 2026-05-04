import AppCard from '@/Components/App/AppCard';
import type { ReactNode } from 'react';
import { Link } from '@inertiajs/react';

type MenuItem = {
    label: string;
    href?: string;
    action?: 'logout';
    tone?: 'blue' | 'amber' | 'green' | 'red' | 'slate' | 'indigo';
    icon: ReactNode;
};

type MenuSection = {
    title: string;
    items: MenuItem[];
};

type MobileMoreMenuProps = {
    profile: {
        name: string;
        role: string;
        organization: string;
    };
    sections: MenuSection[];
};

const toneClass: Record<NonNullable<MenuItem['tone']>, string> = {
    blue: 'bg-blue-50 text-blue-700',
    amber: 'bg-amber-50 text-amber-700',
    green: 'bg-emerald-50 text-emerald-700',
    red: 'bg-rose-50 text-rose-700',
    slate: 'bg-slate-100 text-slate-700',
    indigo: 'bg-indigo-50 text-indigo-700',
};

export default function MobileMoreMenu({ profile, sections }: MobileMoreMenuProps) {
    return (
        <div className="space-y-4">
            <AppCard>
                <div className="flex items-center gap-3">
                    <div className="h-12 w-12 rounded-full bg-slate-200" />
                    <div>
                        <p className="text-sm font-bold text-slate-900">{profile.name}</p>
                        <p className="text-xs text-slate-500">{profile.role}</p>
                        <p className="text-xs text-slate-500">{profile.organization}</p>
                    </div>
                </div>
            </AppCard>

            {sections.map((section) => (
                <AppCard key={section.title} className="p-0">
                    <div className="border-b border-slate-100 px-5 py-4">
                        <h2 className="text-sm font-bold text-slate-900">{section.title}</h2>
                    </div>
                    <ul className="divide-y divide-slate-100">
                        {section.items.map((item) => {
                            const rowClass = item.action === 'logout' ? 'text-rose-700' : 'text-slate-700';
                            const tone = toneClass[item.tone ?? 'slate'];

                            if (item.action === 'logout') {
                                return (
                                    <li key={item.label}>
                                        <Link href={route('logout')} method="post" as="button" className={`flex w-full items-center justify-between px-5 py-4 text-left ${rowClass}`}>
                                            <span className="flex items-center gap-3">
                                                <span className={`inline-flex h-8 w-8 items-center justify-center rounded-full ${tone}`}>{item.icon}</span>
                                                <span className="text-sm font-semibold">{item.label}</span>
                                            </span>
                                            <span className="text-slate-400">›</span>
                                        </Link>
                                    </li>
                                );
                            }

                            return (
                                <li key={item.label}>
                                    <Link href={item.href ?? '#'} className={`flex items-center justify-between px-5 py-4 ${rowClass}`}>
                                        <span className="flex items-center gap-3">
                                            <span className={`inline-flex h-8 w-8 items-center justify-center rounded-full ${tone}`}>{item.icon}</span>
                                            <span className="text-sm font-semibold">{item.label}</span>
                                        </span>
                                        <span className="text-slate-400">›</span>
                                    </Link>
                                </li>
                            );
                        })}
                    </ul>
                </AppCard>
            ))}
        </div>
    );
}
