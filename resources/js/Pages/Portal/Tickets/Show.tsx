import AttachmentUploader from '@/Components/AttachmentUploader';
import CommentBox from '@/Components/CommentBox';
import TicketPriorityBadge from '@/Components/TicketPriorityBadge';
import TicketStatusBadge from '@/Components/TicketStatusBadge';
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
    return (
        <PortalLayout title={ticket.reference} subtitle={ticket.title}>
            <section className="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                <div className="flex flex-wrap items-center gap-2">
                    <TicketStatusBadge status={ticket.status} />
                    <TicketPriorityBadge priority={ticket.priority} />
                </div>
                <p className="mt-4 text-sm leading-6 text-stone-700">{ticket.description}</p>
            </section>

            <div className="mt-4">
                <TicketTimeline entries={ticket.status_histories} />
            </div>

            <div className="mt-4 grid gap-4 lg:grid-cols-2">
                <div className="space-y-4">
                    <CommentBox storeRoute={route('portal.tickets.comments.store', ticket.id)} />
                    <section className="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                        <h3 className="text-lg font-semibold text-stone-900">Comentarios</h3>
                        <ul className="mt-3 space-y-3 text-sm">
                            {ticket.comments.map((comment) => (
                                <li key={comment.id} className="rounded-xl border border-stone-200 bg-stone-50 p-3">
                                    <p className="text-stone-900">{comment.body}</p>
                                    <p className="mt-1 text-xs text-stone-600">
                                        {comment.user?.name ?? 'Sistema'} - {new Date(comment.created_at).toLocaleString()}
                                    </p>
                                </li>
                            ))}
                        </ul>
                    </section>
                </div>

                <div className="space-y-4">
                    <AttachmentUploader storeRoute={route('portal.tickets.attachments.store', ticket.id)} />
                    <section className="rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                        <h3 className="text-lg font-semibold text-stone-900">Anexos</h3>
                        <ul className="mt-3 space-y-2 text-sm">
                            {ticket.attachments.map((attachment) => (
                                <li key={attachment.id} className="rounded-xl border border-stone-200 bg-stone-50 p-3">
                                    <a href={`/storage/${attachment.file_path}`} target="_blank" rel="noreferrer" className="font-medium text-stone-900 hover:text-stone-700">
                                        {attachment.file_name}
                                    </a>
                                </li>
                            ))}
                        </ul>
                    </section>
                </div>
            </div>
        </PortalLayout>
    );
}
