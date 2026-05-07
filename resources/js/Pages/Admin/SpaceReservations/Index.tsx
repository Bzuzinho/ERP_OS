import AppCard from '@/Components/App/AppCard';
import EmptyState from '@/Components/App/EmptyState';
import SpaceReservationFilters from '@/Components/SpaceReservationFilters';
import SpaceReservationStatusBadge from '@/Components/SpaceReservationStatusBadge';
import AdminLayout from '@/Layouts/AdminLayout';
import { Link } from '@inertiajs/react';

type Reservation = { id: number; purpose: string; start_at: string; end_at: string; status: string; space?: { id: number; name: string } | null; contact?: { id: number; name: string } | null; event?: { id: number; title: string } | null };

type Props = { reservations: { data: Reservation[] }; spaces: { id: number; name: string }[]; statuses: string[]; };

export default function AdminSpaceReservationsIndex({ reservations, spaces, statuses }: Props) {
    return (
        <AdminLayout
            title="Reservas de Espacos"
            subtitle="Pedidos e reservas aprovadas"
            headerActions={<Link href={route('admin.space-reservations.create')} className="inline-flex rounded-2xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Criar reserva</Link>}
        >
            <SpaceReservationFilters statuses={statuses} spaces={spaces} indexRouteName="admin.space-reservations.index" />

            <div className="mt-4 hidden overflow-hidden rounded-2xl border border-slate-200 bg-white lg:block">
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
                {reservations.data.length === 0 ? (
                    <div className="p-6">
                        <EmptyState title="Sem reservas" description="Nao existem reservas para os filtros atuais." actionLabel="Criar reserva" actionHref={route('admin.space-reservations.create')} />
                    </div>
                ) : null}
            </div>

            <div className="mt-4 grid gap-3 lg:hidden">
                {reservations.data.map((reservation) => (
                    <AppCard key={reservation.id} className="p-4">
                        <Link href={route('admin.space-reservations.show', reservation.id)} className="flex items-start justify-between gap-3">
                            <div className="min-w-0">
                                <p className="truncate text-sm font-semibold text-slate-900">{reservation.space?.name ?? 'Espaco sem nome'}</p>
                                <p className="mt-1 line-clamp-2 text-sm text-slate-700">{reservation.purpose}</p>
                                <p className="mt-2 text-xs text-slate-500">{new Date(reservation.start_at).toLocaleString()} - {new Date(reservation.end_at).toLocaleString()}</p>
                                <p className="mt-1 text-xs text-slate-500">Requerente: {reservation.contact?.name ?? '-'}</p>
                            </div>
                            <span className="text-slate-400">›</span>
                        </Link>
                        <div className="mt-3 flex flex-wrap items-center gap-2">
                            <SpaceReservationStatusBadge status={reservation.status} />
                            {reservation.event?.title ? <span className="text-xs text-slate-500">Evento: {reservation.event.title}</span> : null}
                        </div>
                    </AppCard>
                ))}

                {reservations.data.length === 0 ? (
                    <EmptyState title="Sem reservas" description="Nao existem reservas para os filtros atuais." actionLabel="Criar reserva" actionHref={route('admin.space-reservations.create')} />
                ) : null}
            </div>
        </AdminLayout>
    );
}
