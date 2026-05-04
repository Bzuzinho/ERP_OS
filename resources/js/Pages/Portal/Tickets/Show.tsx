import AttachmentUploader from '@/Components/AttachmentUploader';
import AppBadge from '@/Components/App/AppBadge';
import AppCard from '@/Components/App/AppCard';
import CommentBox from '@/Components/CommentBox';
import TicketTimeline from '@/Components/TicketTimeline';
import PortalLayout from '@/Layouts/PortalLayout';

type Ticket = {
    id: number;
    reference: string;
    title: string;
    description: string;
    status: string;
    priority: string;
    status_histories: Array<{
        id: number;
        old_status: string | null;
        new_status: string;
        notes: string | null;
        created_at: string;
        changed_by?: { id: number; name: string } | null;
    }>;
    comments: Array<{
        id: number;
        body: string;
        created_at: string;
        visibility: string;
        user?: { id: number; name: string } | null;
    }>;
    attachments: Array<{
        id: number;
        file_name: string;
        file_path: string;
        visibility: string;
    }>;
};

type Props = {
    ticket: Ticket;
};

export default function PortalTicketsShow({ ticket }: Props) {
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
        <PortalLayout title="Pedido" subtitle={ticket.reference}>
            <AppCard>
                <p className="text-sm font-bold text-blue-700">{ticket.reference}</p>
                <h2 className="mt-2 text-xl font-bold text-slate-900">{ticket.title}</h2>
                <div className="flex flex-wrap items-center gap-2">
                    <AppBadge tone={statusTone(ticket.status)}>{ticket.status}</AppBadge>
                    <AppBadge tone={priorityTone(ticket.priority)}>{ticket.priority}</AppBadge>
                </div>
            </AppCard>

            <AppCard className="mt-4">
                <h3 className="text-base font-bold text-slate-900">Descrição</h3>
                <p className="mt-3 text-sm leading-6 text-slate-700">{ticket.description}</p>
            </AppCard>

            <div className="mt-4 rounded-3xl border border-slate-200/70 bg-white p-5 shadow-sm">
                <h3 className="mb-3 text-base font-bold text-slate-900">Linha temporal</h3>
                <TicketTimeline entries={ticket.status_histories} />
            </div>

            <div className="mt-4 grid gap-4 lg:grid-cols-2">
                <div className="space-y-4">
                    <CommentBox storeRoute={route('portal.tickets.comments.store', ticket.id)} />
                    <section className="rounded-3xl border border-slate-200/70 bg-white p-5 shadow-sm">
                        <h3 className="text-lg font-semibold text-slate-900">Comentarios</h3>
                        <ul className="mt-3 space-y-3 text-sm">
                            {ticket.comments.map((comment) => (
                                <li key={comment.id} className="rounded-2xl border border-slate-100 bg-slate-50 p-3.5">
                                    <p className="text-slate-900">{comment.body}</p>
                                    <p className="mt-1 text-xs text-slate-600">
                                        {comment.user?.name ?? 'Sistema'} - {new Date(comment.created_at).toLocaleString()}
                                    </p>
                                </li>
                            ))}
                        </ul>
                    </section>
                </div>

                <div className="space-y-4">
                    <AttachmentUploader storeRoute={route('portal.tickets.attachments.store', ticket.id)} />
                    <section className="rounded-3xl border border-slate-200/70 bg-white p-5 shadow-sm">
                        <h3 className="text-lg font-semibold text-slate-900">Anexos</h3>
                        <ul className="mt-3 space-y-2 text-sm">
                            {ticket.attachments.map((attachment) => (
                                <li key={attachment.id} className="rounded-2xl border border-slate-100 bg-slate-50 p-3.5">
                                    <a href={`/storage/${attachment.file_path}`} target="_blank" rel="noreferrer" className="font-medium text-slate-900 hover:text-slate-700">
                                        {attachment.file_name}
                                    </a>
                                </li>
                            ))}
                        </ul>
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
        </PortalLayout>
    );
}
