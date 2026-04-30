import SpaceReservationFilters from '@/Components/SpaceReservationFilters';
import SpaceReservationStatusBadge from '@/Components/SpaceReservationStatusBadge';
import AdminLayout from '@/Layouts/AdminLayout';
import { Link } from '@inertiajs/react';

type Reservation = { id: number; purpose: string; start_at: string; end_at: string; status: string; space?: { id: number; name: string } | null; contact?: { id: number; name: string } | null; event?: { id: number; title: string } | null };

type Props = { reservations: { data: Reservation[] }; spaces: { id: number; name: string }[]; statuses: string[]; };

export default function AdminSpaceReservationsIndex({ reservations, spaces, statuses }: Props) {
    return (
        <AdminLayout title="Reservas de Espacos" subtitle="Pedidos e reservas aprovadas" headerActions={<Link href={route('admin.space-reservations.create')} className="inline-flex rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white">Criar Reserva</Link>}>
            <SpaceReservationFilters statuses={statuses} spaces={spaces} indexRouteName="admin.space-reservations.index" />
            <div className="mt-4 overflow-hidden rounded-2xl border border-slate-200 bg-white">
                <table className="min-w-full divide-y divide-slate-200 text-sm">
                    <thead className="bg-slate-50 text-left text-slate-600"><tr><th className="px-4 py-3">Espaco</th><th className="px-4 py-3">Requerente</th><th className="px-4 py-3">Finalidade</th><th className="px-4 py-3">Periodo</th><th className="px-4 py-3">Estado</th><th className="px-4 py-3">Evento</th></tr></thead>
                    <tbody className="divide-y divide-slate-100">
                        {reservations.data.map((reservation) => (
                            <tr key={reservation.id}>
                                <td className="px-4 py-3"><Link href={route('admin.space-reservations.show', reservation.id)}>{reservation.space?.name ?? '-'}</Link></td>
                                <td className="px-4 py-3">{reservation.contact?.name ?? '-'}</td>
                                <td className="px-4 py-3">{reservation.purpose}</td>
                                <td className="px-4 py-3">{new Date(reservation.start_at).toLocaleString()} - {new Date(reservation.end_at).toLocaleString()}</td>
                                <td className="px-4 py-3"><SpaceReservationStatusBadge status={reservation.status} /></td>
                                <td className="px-4 py-3">{reservation.event?.title ?? '-'}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AdminLayout>
    );
}
