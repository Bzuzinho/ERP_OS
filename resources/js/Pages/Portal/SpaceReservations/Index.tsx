import SpaceReservationStatusBadge from '@/Components/SpaceReservationStatusBadge';
import PortalLayout from '@/Layouts/PortalLayout';
import { Link } from '@inertiajs/react';

type Reservation = { id: number; purpose: string; start_at: string; end_at: string; status: string; space?: { id: number; name: string } | null };
type Props = { reservations: { data: Reservation[] } };

export default function PortalSpaceReservationsIndex({ reservations }: Props) {
    return (
        <PortalLayout title="As minhas reservas" subtitle="Pedidos de reserva submetidos" headerActions={<Link href={route('portal.space-reservations.create')} className="inline-flex rounded-xl bg-amber-600 px-4 py-2 text-sm font-medium text-white">Pedir reserva</Link>}>
            <div className="space-y-3">
                {reservations.data.map((reservation) => (
                    <article key={reservation.id} className="rounded-2xl border border-stone-200 bg-white p-4 text-sm">
                        <Link href={route('portal.space-reservations.show', reservation.id)} className="font-semibold text-stone-900">{reservation.space?.name ?? '-'} - {reservation.purpose}</Link>
                        <p className="text-stone-700">{new Date(reservation.start_at).toLocaleString()} - {new Date(reservation.end_at).toLocaleString()}</p>
                        <div className="mt-2"><SpaceReservationStatusBadge status={reservation.status} /></div>
                    </article>
                ))}
            </div>
        </PortalLayout>
    );
}
