import SpaceStatusBadge from '@/Components/SpaceStatusBadge';

type Props = {
    name: string;
    status: string;
    location_text?: string | null;
    capacity?: number | null;
    is_public?: boolean;
};

export default function SpaceSummaryCard({ name, status, location_text, capacity, is_public }: Props) {
    return (
        <section className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div className="flex items-center justify-between">
                <h2 className="text-lg font-semibold text-slate-950">{name}</h2>
                <SpaceStatusBadge status={status} />
            </div>
            <p className="mt-2 text-sm text-slate-600">{location_text ?? 'Sem local definido'}</p>
            <p className="mt-1 text-sm text-slate-600">Capacidade: {capacity ?? '-'}</p>
            <p className="mt-1 text-sm text-slate-600">Publico: {is_public ? 'Sim' : 'Nao'}</p>
        </section>
    );
}
