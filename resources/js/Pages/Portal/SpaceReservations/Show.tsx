import SpaceReservationStatusBadge from '@/Components/SpaceReservationStatusBadge';
import SpaceReservationTimeline from '@/Components/SpaceReservationTimeline';
import PortalLayout from '@/Layouts/PortalLayout';
import { useForm } from '@inertiajs/react';

type Reservation = {
    id: number;
    purpose: string;
    status: string;
    start_at: string;
    end_at: string;
    notes: string | null;
    space?: { id: number; name: string; location_text?: string | null } | null;
    approvals: { id: number; action: string; old_status: string | null; new_status: string; notes: string | null; created_at: string; decided_by?: { id: number; name: string } | null }[];
};

type Props = { reservation: Reservation; canCancel: boolean };

export default function PortalSpaceReservationsShow({ reservation, canCancel }: Props) {
    const cancelForm = useForm({ cancellation_reason: '' });

    return (
        <PortalLayout title="Detalhe da Reserva" subtitle={reservation.purpose}>
            <section className="rounded-2xl border border-stone-200 bg-white p-4 text-sm text-stone-700">
                <p className="font-semibold text-stone-900">{reservation.space?.name ?? '-'}</p>
                <p>{new Date(reservation.start_at).toLocaleString()} - {new Date(reservation.end_at).toLocaleString()}</p>
                <div className="mt-2"><SpaceReservationStatusBadge status={reservation.status} /></div>
                <p className="mt-2">{reservation.notes ?? '-'}</p>
            </section>
            {canCancel ? <button onClick={() => cancelForm.post(route('portal.space-reservations.cancel', reservation.id))} className="mt-4 w-full rounded-xl bg-rose-700 px-4 py-3 text-sm font-semibold text-white active:bg-rose-800 sm:w-auto">Cancelar reserva</button> : null}
            <section className="mt-4 rounded-2xl border border-stone-200 bg-stone-50 p-4">
                <p className="mb-2 font-semibold text-stone-900">Historico</p>
                <SpaceReservationTimeline approvals={reservation.approvals ?? []} />
            </section>
        </PortalLayout>
    );
}
