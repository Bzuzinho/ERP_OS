import AttachmentUploader from '@/Components/AttachmentUploader';
import CommentBox from '@/Components/CommentBox';
import ContactSummaryCard from '@/Components/ContactSummaryCard';
import TicketPriorityBadge from '@/Components/TicketPriorityBadge';
import TicketStatusBadge from '@/Components/TicketStatusBadge';
import TicketTimeline from '@/Components/TicketTimeline';
import AdminLayout from '@/Layouts/AdminLayout';
import { Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type UserRef = { id: number; name: string };

type Ticket = {
    id: number;
    reference: string;
    title: string;
    description: string;
    status: string;
    priority: string;
    source: string;
    due_date: string | null;
    visibility: string;
    assignee: UserRef | null;
    contact: {
        id: number;
        name: string;
        email?: string | null;
        phone?: string | null;
        mobile?: string | null;
    } | null;
    status_histories: Array<{
        id: number;
        old_status: string | null;
        new_status: string;
        notes: string | null;
        created_at: string;
        changed_by: UserRef | null;
    }>;
    comments: Array<{
        id: number;
        body: string;
        visibility: string;
        created_at: string;
        user: UserRef | null;
    }>;
    attachments: Array<{
        id: number;
        file_name: string;
        file_path: string;
        visibility: string;
        uploaded_by: number | null;
    }>;
};

type Props = {
    ticket: Ticket;
    statuses: string[];
    users: UserRef[];
};

export default function TicketsShow({ ticket, statuses, users }: Props) {
    const statusForm = useForm({ status: ticket.status, notes: '' });
    const assignForm = useForm({ assigned_to: ticket.assignee?.id ? String(ticket.assignee.id) : '' });

    const submitStatus = (event: FormEvent) => {
        event.preventDefault();
        statusForm.patch(route('admin.tickets.status.update', ticket.id));
    };

    const submitAssign = (event: FormEvent) => {
        event.preventDefault();
        assignForm.patch(route('admin.tickets.assign', ticket.id));
    };

    return (
        <AdminLayout
            title={ticket.reference}
            subtitle={ticket.title}
            headerActions={
                <Link href={route('admin.tickets.edit', ticket.id)} className="inline-flex rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                    Editar Pedido
                </Link>
            }
        >
            <div className="grid gap-4 lg:grid-cols-3">
                <section className="rounded-2xl border border-slate-200 bg-white p-5 lg:col-span-2">
                    <div className="flex flex-wrap items-center gap-2">
                        <TicketStatusBadge status={ticket.status} />
                        <TicketPriorityBadge priority={ticket.priority} />
                    </div>
                    <p className="mt-4 text-sm leading-6 text-slate-700">{ticket.description}</p>
                </section>

                <ContactSummaryCard contact={ticket.contact} />
            </div>

            <div className="mt-4 grid gap-4 lg:grid-cols-2">
                <form onSubmit={submitStatus} className="rounded-2xl border border-slate-200 bg-white p-4">
                    <h3 className="text-lg font-semibold text-slate-900">Atualizar estado</h3>
                    <div className="mt-3 flex flex-col gap-3">
                        <select value={statusForm.data.status} onChange={(event) => statusForm.setData('status', event.target.value)} className="rounded-xl border border-slate-300 px-3 py-2 text-sm">
                            {statuses.map((status) => <option key={status} value={status}>{status}</option>)}
                        </select>
                        <textarea value={statusForm.data.notes} onChange={(event) => statusForm.setData('notes', event.target.value)} placeholder="Notas" className="min-h-24 rounded-xl border border-slate-300 px-3 py-2 text-sm" />
                        <button type="submit" className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Atualizar estado</button>
                    </div>
                </form>

                <form onSubmit={submitAssign} className="rounded-2xl border border-slate-200 bg-white p-4">
                    <h3 className="text-lg font-semibold text-slate-900">Atribuir responsavel</h3>
                    <div className="mt-3 flex items-center gap-3">
                        <select value={assignForm.data.assigned_to} onChange={(event) => assignForm.setData('assigned_to', event.target.value)} className="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm">
                            <option value="">Sem responsavel</option>
                            {users.map((user) => <option key={user.id} value={user.id}>{user.name}</option>)}
                        </select>
                        <button type="submit" className="rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">Guardar</button>
                    </div>
                </form>
            </div>

            <div className="mt-4">
                <TicketTimeline entries={ticket.status_histories} />
            </div>

            <div className="mt-4 grid gap-4 lg:grid-cols-2">
                <div className="space-y-4">
                    <CommentBox storeRoute={route('admin.tickets.comments.store', ticket.id)} canSetVisibility />
                    <section className="rounded-2xl border border-slate-200 bg-white p-4">
                        <h3 className="text-lg font-semibold text-slate-900">Comentarios</h3>
                        <ul className="mt-3 space-y-3 text-sm">
                            {ticket.comments.map((comment) => (
                                <li key={comment.id} className="rounded-xl border border-slate-200 bg-slate-50 p-3">
                                    <p className="text-slate-900">{comment.body}</p>
                                    <p className="mt-1 text-xs text-slate-600">
                                        {comment.user?.name ?? 'Sistema'} - {new Date(comment.created_at).toLocaleString()} - {comment.visibility}
                                    </p>
                                </li>
                            ))}
                        </ul>
                    </section>
                </div>

                <div className="space-y-4">
                    <AttachmentUploader storeRoute={route('admin.tickets.attachments.store', ticket.id)} canSetVisibility />
                    <section className="rounded-2xl border border-slate-200 bg-white p-4">
                        <h3 className="text-lg font-semibold text-slate-900">Anexos</h3>
                        <ul className="mt-3 space-y-2 text-sm">
                            {ticket.attachments.map((attachment) => (
                                <li key={attachment.id} className="rounded-xl border border-slate-200 bg-slate-50 p-3">
                                    <a href={`/storage/${attachment.file_path}`} target="_blank" rel="noreferrer" className="font-medium text-slate-900 hover:text-slate-700">
                                        {attachment.file_name}
                                    </a>
                                    <p className="mt-1 text-xs text-slate-600">{attachment.visibility}</p>
                                </li>
                            ))}
                        </ul>
                    </section>
                </div>
            </div>
        </AdminLayout>
    );
}
