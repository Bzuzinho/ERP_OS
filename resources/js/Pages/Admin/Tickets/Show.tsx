import AttachmentList from '@/Components/App/AttachmentList';
import AttachmentUploader from '@/Components/AttachmentUploader';
import AppBadge from '@/Components/App/AppBadge';
import AppCard from '@/Components/App/AppCard';
import CommentBox from '@/Components/CommentBox';
import ContactSummaryCard from '@/Components/ContactSummaryCard';
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
    created_at?: string | null;
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

    const statusTone = (status: string): 'blue' | 'amber' | 'green' | 'red' | 'slate' => {
        const normalized = status.toLowerCase();
        if (normalized.includes('fech') || normalized.includes('resol')) return 'green';
        if (normalized.includes('urgent')) return 'red';
        if (normalized.includes('pend') || normalized.includes('anal')) return 'amber';
        if (normalized.includes('novo') || normalized.includes('abert') || normalized.includes('exec')) return 'blue';
        return 'slate';
    };

    const priorityTone = (priority: string): 'blue' | 'amber' | 'green' | 'red' | 'slate' => {
        const normalized = priority.toLowerCase();
        if (normalized.includes('alta') || normalized.includes('urgent')) return 'red';
        if (normalized.includes('media') || normalized.includes('média')) return 'amber';
        if (normalized.includes('baixa')) return 'green';
        return 'slate';
    };

    return (
        <AdminLayout
            title="Pedido"
            subtitle={ticket.reference}
            headerActions={
                <Link href={route('admin.tickets.edit', ticket.id)} className="inline-flex rounded-2xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                    Editar Pedido
                </Link>
            }
        >
            <div className="grid gap-4 lg:grid-cols-3">
                <AppCard className="lg:col-span-2">
                    <p className="text-sm font-bold text-blue-700">{ticket.reference}</p>
                    <h2 className="mt-2 text-xl font-bold text-slate-900">{ticket.title}</h2>
                    <p className="mt-1 text-sm text-slate-500">Categoria: {ticket.source || 'Geral'}</p>
                    <div className="flex flex-wrap items-center gap-2">
                        <AppBadge tone={statusTone(ticket.status)}>{ticket.status}</AppBadge>
                        <AppBadge tone={priorityTone(ticket.priority)}>{ticket.priority}</AppBadge>
                        <span className="text-xs text-slate-500">{ticket.created_at ? `Criado em ${new Date(ticket.created_at).toLocaleDateString()}` : 'Data de criação indisponível'}</span>
                    </div>
                </AppCard>

                <ContactSummaryCard contact={ticket.contact} />
            </div>

            <AppCard className="mt-4">
                <h3 className="text-base font-bold text-slate-900">Descrição</h3>
                <p className="mt-3 text-sm leading-6 text-slate-700">{ticket.description}</p>
            </AppCard>

            <div className="mt-4 grid gap-4 lg:grid-cols-2">
                <form onSubmit={submitStatus} className="rounded-3xl border border-slate-200/70 bg-white p-5 shadow-sm">
                    <h3 className="text-lg font-semibold text-slate-900">Atualizar estado</h3>
                    <div className="mt-3 flex flex-col gap-3">
                        <select value={statusForm.data.status} onChange={(event) => statusForm.setData('status', event.target.value)} className="rounded-2xl border border-slate-300 px-3 py-2.5 text-sm">
                            {statuses.map((status) => <option key={status} value={status}>{status}</option>)}
                        </select>
                        <textarea value={statusForm.data.notes} onChange={(event) => statusForm.setData('notes', event.target.value)} placeholder="Notas" className="min-h-24 rounded-2xl border border-slate-300 px-3 py-2.5 text-sm" />
                        <button type="submit" className="rounded-2xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Atualizar estado</button>
                    </div>
                </form>

                <form onSubmit={submitAssign} className="rounded-3xl border border-slate-200/70 bg-white p-5 shadow-sm">
                    <h3 className="text-lg font-semibold text-slate-900">Atribuir responsavel</h3>
                    <div className="mt-3 flex items-center gap-3">
                        <select value={assignForm.data.assigned_to} onChange={(event) => assignForm.setData('assigned_to', event.target.value)} className="w-full rounded-2xl border border-slate-300 px-3 py-2.5 text-sm">
                            <option value="">Sem responsavel</option>
                            {users.map((user) => <option key={user.id} value={user.id}>{user.name}</option>)}
                        </select>
                        <button type="submit" className="rounded-2xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Guardar</button>
                    </div>
                </form>
            </div>

            <div className="mt-4 rounded-3xl border border-slate-200/70 bg-white p-5 shadow-sm">
                <h3 className="mb-3 text-base font-bold text-slate-900">Linha temporal</h3>
                <TicketTimeline entries={ticket.status_histories} />
            </div>

            <div className="mt-4 grid gap-4 lg:grid-cols-2">
                <div className="space-y-4">
                    <CommentBox storeRoute={route('admin.tickets.comments.store', ticket.id)} canSetVisibility />
                    <section className="rounded-3xl border border-slate-200/70 bg-white p-5 shadow-sm">
                        <h3 className="text-lg font-semibold text-slate-900">Comentarios</h3>
                        <ul className="mt-3 space-y-3 text-sm">
                            {ticket.comments.map((comment) => (
                                <li key={comment.id} className="rounded-2xl border border-slate-100 bg-slate-50 p-3.5">
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
                    <section className="rounded-3xl border border-slate-200/70 bg-white p-5 shadow-sm">
                        <h3 className="text-lg font-semibold text-slate-900">Anexos</h3>
                        <AttachmentList attachments={ticket.attachments} downloadRouteName="admin.attachments.download" />
                    </section>
                </div>
            </div>

            <div className="fixed bottom-20 left-4 right-4 z-30 grid grid-cols-2 gap-3 rounded-2xl border border-slate-200 bg-white p-3 shadow-lg lg:hidden">
                <button type="button" className="rounded-xl border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-semibold text-blue-700">
                    Adicionar comentário
                </button>
                <button type="button" className="rounded-xl bg-blue-600 px-3 py-2 text-sm font-semibold text-white">
                    Atualizar
                </button>
            </div>
        </AdminLayout>
    );
}
