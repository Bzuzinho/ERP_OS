import AdminLayout from '@/Layouts/AdminLayout';
import { useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';

type Reservation = { id: number; purpose: string; notes: string | null; internal_notes: string | null; start_at: string; end_at: string; status: string; contact_id: number | null; space_id: number; };
type Props = { reservation: Reservation; statuses: string[] };

export default function AdminSpaceReservationsEdit({ reservation, statuses }: Props) {
    const { data, setData, put, processing } = useForm({
        start_at: reservation.start_at?.slice(0, 16) ?? '',
        end_at: reservation.end_at?.slice(0, 16) ?? '',
        purpose: reservation.purpose,
        notes: reservation.notes ?? '',
        internal_notes: reservation.internal_notes ?? '',
        status: reservation.status,
        contact_id: reservation.contact_id ?? 0,
        space_id: reservation.space_id,
    });

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        put(route('admin.space-reservations.update', reservation.id));
    };

    return (
        <AdminLayout title="Editar Reserva" subtitle={reservation.purpose}>
            <form onSubmit={submit} className="space-y-3 rounded-2xl border border-slate-200 bg-white p-4">
                <input value={data.purpose} onChange={(event) => setData('purpose', event.target.value)} className="w-full rounded-lg border-slate-300 text-sm" />
                <select value={data.status} onChange={(event) => setData('status', event.target.value)} className="w-full rounded-lg border-slate-300 text-sm">{statuses.map((status) => <option key={status} value={status}>{status}</option>)}</select>
                <button disabled={processing} className="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white">Atualizar</button>
            </form>
        </AdminLayout>
    );
}
