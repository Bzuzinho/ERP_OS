import PublicActivityCard from '@/Components/PublicActivityCard';
import PortalLayout from '@/Layouts/PortalLayout';
import { Link, router } from '@inertiajs/react';

export default function PortalOperationalPlansIndex({ plans, filters, types }: { plans: { data: any[] }; filters: Record<string, string | null | undefined>; types: string[] }) {
    const onFilterChange = (name: string, value: string) => {
        router.get(route('portal.operational-plans.index'), { ...filters, [name]: value || undefined }, { preserveState: true, replace: true });
    };

    return (
        <PortalLayout title="Atividades Públicas" subtitle="Acompanhe atividades e ações no território">
            <div className="grid gap-3 rounded-2xl border border-stone-200 bg-white p-4 md:grid-cols-3">
                <select value={filters.plan_type ?? ''} onChange={(e) => onFilterChange('plan_type', e.target.value)} className="rounded-lg border-stone-300">
                    <option value="">Tipo</option>
                    {types.map((type) => <option key={type} value={type}>{type}</option>)}
                </select>
                <input type="date" value={filters.start_date ?? ''} onChange={(e) => onFilterChange('start_date', e.target.value)} className="rounded-lg border-stone-300" />
                <input type="date" value={filters.end_date ?? ''} onChange={(e) => onFilterChange('end_date', e.target.value)} className="rounded-lg border-stone-300" />
            </div>

            <div className="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                {plans.data.map((plan) => (
                    <Link key={plan.id} href={route('portal.operational-plans.show', plan.id)} className="block"><PublicActivityCard activity={plan} /></Link>
                ))}
                {plans.data.length === 0 ? <p className="text-sm text-stone-500">Sem atividades públicas para mostrar.</p> : null}
            </div>
        </PortalLayout>
    );
}
