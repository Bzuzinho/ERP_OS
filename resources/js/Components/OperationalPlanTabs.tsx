import { Link } from '@inertiajs/react';

type Props = { active: 'plans' | 'recurring' | 'public' };

export default function OperationalPlanTabs({ active }: Props) {
    const base = 'rounded-full px-4 py-2 text-sm font-medium';

    return (
        <div className="flex flex-wrap gap-2">
            <Link href={route('admin.operational-plans.index')} className={`${base} ${active === 'plans' ? 'bg-slate-900 text-white' : 'border border-slate-300 text-slate-700'}`}>Planos</Link>
            <Link href={route('admin.recurring-operations.index')} className={`${base} ${active === 'recurring' ? 'bg-slate-900 text-white' : 'border border-slate-300 text-slate-700'}`}>Recorrências</Link>
            <Link href={route('portal.operational-plans.index')} className={`${base} ${active === 'public' ? 'bg-slate-900 text-white' : 'border border-slate-300 text-slate-700'}`}>Atividades públicas</Link>
        </div>
    );
}
