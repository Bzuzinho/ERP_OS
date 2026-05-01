type Activity = {
    id: number;
    title: string;
    description?: string | null;
    start_date: string;
    end_date?: string | null;
    plan_type: string;
    status: string;
    related_space?: { id: number; name: string; location_text?: string | null } | null;
};

type Props = { activity: Activity };

export default function PublicActivityCard({ activity }: Props) {
    return (
        <article className="rounded-3xl border border-stone-200 bg-white p-5 shadow-sm">
            <p className="text-xs uppercase tracking-[0.2em] text-amber-700">{activity.plan_type}</p>
            <h3 className="mt-2 text-lg font-semibold text-stone-950">{activity.title}</h3>
            <p className="mt-2 text-sm text-stone-600">{activity.description ?? 'Sem descrição pública.'}</p>
            <p className="mt-3 text-xs text-stone-500">{activity.start_date}{activity.end_date ? ` → ${activity.end_date}` : ''}</p>
            {activity.related_space?.name ? <p className="mt-1 text-xs text-stone-500">Local: {activity.related_space.name}</p> : null}
            <p className="mt-2 inline-flex rounded-full bg-stone-100 px-2 py-1 text-xs font-medium text-stone-700">{activity.status}</p>
        </article>
    );
}
