import AdminSidebar from '@/Components/App/AdminSidebar';
import DesktopTopbar from '@/Components/App/DesktopTopbar';
import MobileBottomNav from '@/Components/App/MobileBottomNav';
import MobileHeader from '@/Components/App/MobileHeader';
import type { ReactNode } from 'react';

type NavItem = {
    label: string;
    href: string;
    activePatterns: string[];
    icon: ReactNode;
};

type AppShellProps = {
    title: string;
    subtitle?: string;
    organizationLabel: string;
    organizationLogoUrl?: string | null;
    organizationHref: string;
    desktopNav: NavItem[];
    mobileNav: NavItem[];
    children: ReactNode;
    showBackOnMobile?: boolean;
    mobileBackHref?: string;
};

export default function AppShell({
    title,
    subtitle,
    organizationLabel,
    organizationLogoUrl,
    organizationHref,
    desktopNav,
    mobileNav,
    children,
    showBackOnMobile = false,
    mobileBackHref,
}: AppShellProps) {
    return (
        <div className="min-h-screen overflow-x-hidden bg-slate-50 text-slate-900">
            <MobileHeader title={title} subtitle={subtitle} showBack={showBackOnMobile} backHref={mobileBackHref} />

            <AdminSidebar
                items={desktopNav.map((item) => ({
                    label: item.label,
                    href: item.href,
                    patterns: item.activePatterns,
                    icon: item.icon,
                }))}
                organizationLabel={organizationLabel}
                organizationLogoUrl={organizationLogoUrl}
                organizationHref={organizationHref}
            />

            <div className="min-w-0 pb-24 lg:ml-[260px] lg:pb-0">
                <DesktopTopbar />
                <main className="w-full min-w-0 overflow-hidden px-4 pb-8 pt-5 sm:px-6 lg:px-8 lg:pt-8">{children}</main>
            </div>

            <MobileBottomNav items={mobileNav.map((item) => ({ label: item.label, href: item.href, activePatterns: item.activePatterns, icon: item.icon }))} />
        </div>
    );
}
