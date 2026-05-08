import SpaceReservationStatusBadge from '@/Components/SpaceReservationStatusBadge';
import SpaceReservationTimeline from '@/Components/SpaceReservationTimeline';
import AdminLayout from '@/Layouts/AdminLayout';
import { useForm } from '@inertiajs/react';

type Reservation = {
    id: number;
    purpose: string;
    start_at: string;
    end_at: string;
    status: string;
    notes: string | null;
    internal_notes: string | null;
    space?: { id: number; name: string } | null;
    contact?: { id: number; name: string } | null;
    event?: { id: number; title: string; event_type: string; status: string; start_at: string; end_at: string } | null;
    tasks?: { id: number; title: string; status: string; priority: string; due_date: string | null; assignee?: { id: number; name: string } | null }[];
    approvals: { id: number; action: string; old_status: string | null; new_status: string; notes: string | null; created_at: string; decided_by?: { id: number; name: string } | null; }[];
};

type Props = { reservation: Reservation; can: { approve: boolean; cancel: boolean; update: boolean } };

export default function AdminSpaceReservationsShow({ reservation, can }: Props) {
    const approveForm = useForm({ notes: '' });
    const rejectForm = useForm({ rejection_reason: '' });
    const cancelForm = useForm({ cancellation_reason: '' });

    return (
        <AdminLayout title="Detalhe da Reserva" subtitle={reservation.purpose}>
            <section className="rounded-2xl border border-slate-200 bg-white p-4 text-sm text-slate-700">
                <p className="font-semibold text-slate-900">Espaco: {reservation.space?.name ?? '-'}</p>
                <p>Requerente: {reservation.contact?.name ?? '-'}</p>
                <p>Periodo: {new Date(reservation.start_at).toLocaleString()} - {new Date(reservation.end_at).toLocaleString()}</p>
                <div className="mt-2"><SpaceReservationStatusBadge status={reservation.status} /></div>
                <p className="mt-2">Notas: {reservation.notes ?? '-'}</p>
                <p className="mt-1">Notas internas: {reservation.internal_notes ?? '-'}</p>
                <p className="mt-1">Evento: {reservation.event?.title ?? '-'}</p>
                <p className="mt-1">Tipo de evento: {reservation.event?.event_type ?? '-'}</p>
                <p className="mt-1">Estado do evento: {reservation.event?.status ?? '-'}</p>
                {reservation.event ? <p className="mt-1">Agenda: {new Date(reservation.event.start_at).toLocaleString()} - {new Date(reservation.event.end_at).toLocaleString()}</p> : null}
                {reservation.status === 'approved' ? <p className="mt-2 rounded-lg bg-emerald-50 px-3 py-2 text-emerald-700">Reserva aprovada bloqueia agenda do espaco no periodo indicado.</p> : null}
            </section>
            {can.approve ? (
                <div className="mt-4 flex flex-wrap gap-2">
                    <button onClick={() => approveForm.post(route('admin.space-reservations.approve', reservation.id))} className="rounded-lg bg-emerald-700 px-3 py-2 text-xs font-medium text-white">Aprovar</button>
                    <button onClick={() => rejectForm.post(route('admin.space-reservations.reject', reservation.id))} className="rounded-lg bg-rose-700 px-3 py-2 text-xs font-medium text-white">Rejeitar</button>
                    <button onClick={() => approveForm.post(route('admin.space-reservations.complete', reservation.id))} className="rounded-lg bg-blue-700 px-3 py-2 text-xs font-medium text-white">Concluir</button>
                    {can.cancel ? <button onClick={() => cancelForm.post(route('admin.space-reservations.cancel', reservation.id))} className="rounded-lg bg-slate-700 px-3 py-2 text-xs font-medium text-white">Cancelar</button> : null}
                </div>
            ) : null}
            <section className="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <p className="mb-2 font-semibold text-slate-900">Timeline</p>
                <SpaceReservationTimeline approvals={reservation.approvals ?? []} />
            </section>

            <section className="mt-4 rounded-2xl border border-slate-200 bg-white p-4">
                <p className="font-semibold text-slate-900">Tarefas operacionais associadas</p>
                <ul className="mt-2 space-y-2 text-sm text-slate-700">
                    {(reservation.tasks ?? []).map((task) => (
                        <li key={task.id} className="rounded-lg bg-slate-50 px-3 py-2">
                            <span className="font-medium text-slate-900">{task.title}</span>
                            <span className="ml-2 text-xs text-slate-500">{task.status}</span>
                            <span className="ml-2 text-xs text-slate-500">Prazo: {task.due_date ?? '-'}</span>
                            <span className="ml-2 text-xs text-slate-500">Responsavel: {task.assignee?.name ?? 'Nao atribuido'}</span>
                        </li>
                    ))}
                    {(reservation.tasks ?? []).length === 0 ? <li className="text-slate-500">Sem tarefas associadas.</li> : null}
                </ul>
            </section>
        </AdminLayout>
    );
}
