import SpaceSummaryCard from '@/Components/SpaceSummaryCard';
import SpaceStatusBadge from '@/Components/SpaceStatusBadge';
import AdminLayout from '@/Layouts/AdminLayout';
import { Link, useForm } from '@inertiajs/react';

type ReservationTask = { id: number; title: string; status: string; due_date: string | null; assignee?: { id: number; name: string } | null };
type Reservation = {
    id: number;
    purpose: string;
    status: string;
    start_at: string;
    end_at: string;
    event?: { id: number; title: string; event_type: string; status: string } | null;
    tasks?: ReservationTask[];
};
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

type Props = {
    space: Space;
    currentState: 'free' | 'reserved' | 'maintenance' | 'unavailable';
    can: { reserve: boolean; createMaintenanceTicket: boolean };
};

const stateLabels: Record<Props['currentState'], string> = {
    free: 'Livre',
    reserved: 'Reservado',
    maintenance: 'Manutencao',
    unavailable: 'Indisponivel',
};

export default function AdminSpacesShow({ space, currentState, can }: Props) {
    const maintenanceTicketForm = useForm({
        title: `Manutencao preventiva - ${space.name}`,
        description: `Pedido criado a partir da ficha do espaco ${space.name}.`,
        priority: 'normal',
    });

    return (
        <AdminLayout
            title="Detalhe do Espaco"
            subtitle={space.name}
            headerActions={
                <div className="flex flex-wrap gap-2">
                    {can.reserve ? <Link href={route('admin.space-reservations.create', { space_id: space.id })} className="inline-flex rounded-xl bg-blue-600 px-4 py-2 text-sm font-semibold text-white">Nova Reserva</Link> : null}
                    <Link href={route('admin.spaces.edit', space.id)} className="inline-flex rounded-xl border border-slate-300 px-4 py-2 text-sm">Editar</Link>
                </div>
            }
        >
            <SpaceSummaryCard name={space.name} status={space.status} location_text={space.location_text} capacity={space.capacity} is_public={space.is_public} />
            <section className="mt-4 rounded-2xl border border-slate-200 bg-white p-4 text-sm text-slate-700">
                <p className="font-semibold text-slate-900">Descricao</p>
                <p className="mt-1">{space.description ?? '-'}</p>
                <p className="mt-3 font-semibold text-slate-900">Regras</p>
                <p className="mt-1">{space.rules ?? '-'}</p>
                <div className="mt-3 flex items-center gap-2">
                    <SpaceStatusBadge status={space.status} />
                    <span className="rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">Estado operacional: {stateLabels[currentState]}</span>
                </div>
            </section>

            {can.createMaintenanceTicket ? (
                <section className="mt-4 rounded-2xl border border-slate-200 bg-white p-4">
                    <p className="font-semibold text-slate-900">Criar pedido de manutencao</p>
                    <div className="mt-3 grid gap-3 md:grid-cols-3">
                        <input value={maintenanceTicketForm.data.title} onChange={(event) => maintenanceTicketForm.setData('title', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm" placeholder="Titulo" />
                        <select value={maintenanceTicketForm.data.priority} onChange={(event) => maintenanceTicketForm.setData('priority', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                            <option value="low">Baixa</option>
                            <option value="normal">Normal</option>
                            <option value="high">Alta</option>
                            <option value="urgent">Urgente</option>
                        </select>
                        <button onClick={() => maintenanceTicketForm.post(route('admin.spaces.maintenance-ticket.store', space.id))} className="rounded-xl bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700">Criar pedido de manutencao</button>
                    </div>
                    <textarea value={maintenanceTicketForm.data.description} onChange={(event) => maintenanceTicketForm.setData('description', event.target.value)} className="mt-3 w-full rounded-xl border border-slate-300 px-3 py-2 text-sm" rows={3} placeholder="Descricao do problema" />
                </section>
            ) : null}

            <section className="mt-4 rounded-2xl border border-slate-200 bg-white p-4">
                <p className="font-semibold text-slate-900">Reservas Futuras</p>
                <ul className="mt-2 space-y-2 text-sm">
                    {space.reservations?.map((reservation) => (
                        <li key={reservation.id} className="rounded-xl bg-slate-50 p-3">
                            <div className="flex flex-wrap items-center justify-between gap-2">
                                <span className="font-medium text-slate-900">{reservation.purpose}</span>
                                <span className="text-xs text-slate-600">{reservation.status}</span>
                            </div>
                            <p className="mt-1 text-xs text-slate-600">{new Date(reservation.start_at).toLocaleString()} - {new Date(reservation.end_at).toLocaleString()}</p>
                            <p className="mt-1 text-xs text-slate-600">Evento: {reservation.event?.title ?? 'Nao associado'}</p>
                            <p className="mt-1 text-xs text-slate-600">Tarefas: {reservation.tasks?.length ?? 0}</p>
                        </li>
                    )) ?? null}
                    {(space.reservations ?? []).length === 0 ? <li className="text-slate-500">Sem reservas futuras.</li> : null}
                </ul>
            </section>

            <section className="mt-4 rounded-2xl border border-slate-200 bg-white p-4">
                <p className="font-semibold text-slate-900">Tarefas associadas a reservas</p>
                <ul className="mt-2 space-y-2 text-sm text-slate-700">
                    {(space.reservations ?? []).flatMap((reservation) => reservation.tasks ?? []).map((task) => (
                        <li key={task.id} className="rounded-lg bg-slate-50 px-3 py-2">
                            <span className="font-medium text-slate-900">{task.title}</span>
                            <span className="ml-2 text-xs text-slate-500">{task.status}</span>
                            <span className="ml-2 text-xs text-slate-500">Responsavel: {task.assignee?.name ?? 'Nao atribuido'}</span>
                        </li>
                    ))}
                    {(space.reservations ?? []).flatMap((reservation) => reservation.tasks ?? []).length === 0 ? <li className="text-slate-500">Sem tarefas associadas.</li> : null}
                </ul>
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
