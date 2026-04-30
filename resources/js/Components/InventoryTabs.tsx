import { Link } from '@inertiajs/react';

type Tab = {
    label: string;
    href: string;
    current: string;
};

const tabs: Tab[] = [
    { label: 'Itens', href: route('admin.inventory-items.index'), current: 'admin.inventory-items.*' },
    { label: 'Movimentos', href: route('admin.inventory-movements.index'), current: 'admin.inventory-movements.*' },
    { label: 'Emprestimos', href: route('admin.inventory-loans.index'), current: 'admin.inventory-loans.*' },
    { label: 'Reposicoes', href: route('admin.inventory-restock-requests.index'), current: 'admin.inventory-restock-requests.*' },
    { label: 'Quebras', href: route('admin.inventory-breakages.index'), current: 'admin.inventory-breakages.*' },
    { label: 'Categorias', href: route('admin.inventory-categories.index'), current: 'admin.inventory-categories.*' },
    { label: 'Localizacoes', href: route('admin.inventory-locations.index'), current: 'admin.inventory-locations.*' },
];

export default function InventoryTabs() {
    return (
        <div className="mb-4 flex flex-wrap gap-2">
            {tabs.map((tab) => {
                const active = route().current(tab.current);

                return (
                    <Link
                        key={tab.label}
                        href={tab.href}
                        className={active
                            ? 'rounded-full bg-slate-900 px-4 py-2 text-sm font-medium text-white'
                            : 'rounded-full border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:border-slate-400'}
                    >
                        {tab.label}
                    </Link>
                );
            })}
        </div>
    );
}
