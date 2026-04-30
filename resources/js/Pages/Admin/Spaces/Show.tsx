import SpaceSummaryCard from '@/Components/SpaceSummaryCard';
import SpaceStatusBadge from '@/Components/SpaceStatusBadge';
import AdminLayout from '@/Layouts/AdminLayout';
import { Link } from '@inertiajs/react';

type Reservation = { id: number; purpose: string; status: string; start_at: string; end_at: string };
type Maintenance = { id: number; title: string; status: string };
type Cleaning = { id: number; status: string; scheduled_at: string | null };
type Space = {
    id: number;
    name: string;
    description: string | null;
    location_text: string | null;
    capacity: number | null;
    rules: string | null;
    status: string;
    is_public: boolean;
    reservations: Reservation[];
    maintenance_records: Maintenance[];
    cleaning_records: Cleaning[];
    comments: { id: number; body: string }[];
};

type Props = { space: Space };

export default function AdminSpacesShow({ space }: Props) {
    return (
        <AdminLayout title="Detalhe do Espaco" subtitle={space.name} headerActions={<Link href={route('admin.spaces.edit', space.id)} className="inline-flex rounded-xl border border-slate-300 px-4 py-2 text-sm">Editar</Link>}>
            <SpaceSummaryCard name={space.name} status={space.status} location_text={space.location_text} capacity={space.capacity} is_public={space.is_public} />
            <section className="mt-4 rounded-2xl border border-slate-200 bg-white p-4 text-sm text-slate-700">
                <p className="font-semibold text-slate-900">Descricao</p>
                <p className="mt-1">{space.description ?? '-'}</p>
                <p className="mt-3 font-semibold text-slate-900">Regras</p>
                <p className="mt-1">{space.rules ?? '-'}</p>
                <div className="mt-3"><SpaceStatusBadge status={space.status} /></div>
            </section>
            <section className="mt-4 rounded-2xl border border-slate-200 bg-white p-4">
                <p className="font-semibold text-slate-900">Reservas Futuras</p>
                <ul className="mt-2 space-y-2 text-sm">{space.reservations?.map((reservation) => <li key={reservation.id}>{reservation.purpose} • {reservation.status}</li>) ?? null}</ul>
            </section>
            <section className="mt-4 rounded-2xl border border-slate-200 bg-white p-4">
                <p className="font-semibold text-slate-900">Comentarios</p>
                <ul className="mt-2 space-y-2 text-sm text-slate-700">
                    {(space.comments ?? []).map((comment) => <li key={comment.id} className="rounded-lg bg-slate-50 px-3 py-2">{comment.body}</li>)}
                    {(space.comments ?? []).length === 0 ? <li className="text-slate-500">Sem comentarios.</li> : null}
                </ul>
            </section>
        </AdminLayout>
    );
}
