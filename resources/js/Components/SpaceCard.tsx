import SpaceStatusBadge from '@/Components/SpaceStatusBadge';
import { Link } from '@inertiajs/react';

type Space = {
    id: number;
    name: string;
    capacity: number | null;
    location_text: string | null;
    status: string;
};

type Props = {
    space: Space;
    showReserveButton?: boolean;
};

export default function SpaceCard({ space, showReserveButton = false }: Props) {
    return (
        <article className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div className="flex items-center justify-between gap-2">
                <h3 className="font-semibold text-slate-900">{space.name}</h3>
                <SpaceStatusBadge status={space.status} />
            </div>
            <p className="mt-2 text-sm text-slate-600">{space.location_text ?? 'Local por definir'}</p>
            <p className="mt-1 text-sm text-slate-600">Capacidade: {space.capacity ?? '-'}</p>
            {showReserveButton ? (
                <Link href={route('portal.space-reservations.create')} className="mt-3 inline-flex rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-amber-500">
                    Pedir reserva
                </Link>
            ) : null}
        </article>
    );
}
