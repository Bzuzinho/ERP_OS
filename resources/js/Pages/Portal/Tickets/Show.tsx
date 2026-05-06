import AttachmentList from '@/Components/App/AttachmentList';
import AttachmentUploader from '@/Components/AttachmentUploader';
import AppCard from '@/Components/App/AppCard';
import CommentBox from '@/Components/CommentBox';
import PublicTicketStatusBadge from '@/Components/Portal/PublicTicketStatusBadge';
import { mapTicketStatusToPublicLabel } from '@/Components/Portal/publicTicketStatus';
import TicketTimeline from '@/Components/TicketTimeline';
import PortalLayout from '@/Layouts/PortalLayout';
import { PageProps } from '@/types';
import { usePage } from '@inertiajs/react';

type TimelineEntry = {
    id: number | string;
    kind: 'created' | 'status' | 'assignment' | 'comment' | 'attachment' | 'activity';
    title: string;
    description?: string | null;
    by?: string | null;
    at: string;
    visibility?: 'internal' | 'public' | null;
};

type Ticket = {
    id: number;
    reference: string;
    title: string;
    description: string;
    status: string;
    location_text?: string | null;
    created_at: string;
    updated_at: string;
    status_histories: Array<{
        id: number;
        new_status: string;
        created_at: string;
    }>;
    comments: Array<{
        id: number;
        body: string;
        visibility?: 'internal' | 'public';
        created_at: string;
        user?: { id: number; name: string } | null;
    }>;
    attachments: Array<{
        id: number;
        file_name: string;
        file_path: string;
        visibility?: 'internal' | 'public';
        created_at?: string | null;
    }>;
};

type Props = {
    ticket: Ticket;
};

export default function PortalTicketsShow({ ticket }: Props) {
    const authUserId = usePage<PageProps>().props.auth.user?.id;

    const timelineEntries: TimelineEntry[] = [
        {
            id: `created-${ticket.id}`,
            kind: 'created',
            title: 'Pedido recebido',
            description: ticket.description,
            by: 'Sistema',
            at: ticket.created_at,
        },
        ...ticket.status_histories.map((entry) => ({
            id: `status-${entry.id}`,
            kind: 'status' as const,
            title: `Estado atualizado: ${mapTicketStatusToPublicLabel(entry.new_status)}`,
            by: 'Junta',
            at: entry.created_at,
        })),
        ...ticket.comments.map((comment) => ({
            id: `comment-${comment.id}`,
            kind: 'comment' as const,
            title: comment.user?.id === authUserId ? 'Informacao enviada por si' : 'Resposta da Junta',
            description: comment.body,
            by: comment.user?.name ?? 'Sistema',
            at: comment.created_at,
            visibility: 'public' as const,
        })),
        ...ticket.attachments.map((attachment) => ({
            id: `attachment-${attachment.id}`,
            kind: 'attachment' as const,
            title: `Anexo adicionado: ${attachment.file_name}`,
            by: 'Participante',
            at: attachment.created_at ?? ticket.updated_at,
            visibility: 'public' as const,
        })),
    ];

    return (
        <PortalLayout title={`Pedido ${ticket.reference}`} subtitle="Acompanhe as atualizacoes deste pedido.">
            <AppCard>
                <p className="text-sm font-bold text-blue-700">{ticket.reference}</p>
                <h2 className="mt-2 text-xl font-bold text-slate-900">{ticket.title}</h2>
                <div className="mt-3 flex items-center gap-3">
                    <PublicTicketStatusBadge status={ticket.status} />
                    <p className="text-xs text-slate-500">Ultima atualizacao: {new Date(ticket.updated_at).toLocaleString()}</p>
                </div>
            </AppCard>

            <div className="mt-4 grid gap-4 lg:grid-cols-2">
                <AppCard>
                    <h3 className="text-base font-bold text-slate-900">Dados do pedido</h3>
                    <div className="mt-3 space-y-2 text-sm text-slate-700">
                        <p><span className="font-semibold text-slate-900">Titulo:</span> {ticket.title}</p>
                        <p><span className="font-semibold text-slate-900">Descricao enviada:</span> {ticket.description}</p>
                        {ticket.location_text ? (
                            <p><span className="font-semibold text-slate-900">Localizacao:</span> {ticket.location_text}</p>
                        ) : null}
                        <p><span className="font-semibold text-slate-900">Estado publico:</span> {mapTicketStatusToPublicLabel(ticket.status)}</p>
                        <p><span className="font-semibold text-slate-900">Data de submissao:</span> {new Date(ticket.created_at).toLocaleString()}</p>
                    </div>
                </AppCard>

                <AppCard>
                    <TicketTimeline
                        mode="portal"
                        title="Linha temporal"
                        entries={timelineEntries}
                        emptyText="Sem atualizacoes para mostrar."
                    />
                </AppCard>
            </div>

            <div className="mt-4 grid gap-4 lg:grid-cols-2">
                <div className="space-y-4">
                    <AppCard>
                        <h3 className="text-base font-bold text-slate-900">Comunicacao</h3>
                        <p className="mt-1 text-xs text-slate-600">A sua mensagem sera enviada a Junta e ficara associada a este pedido.</p>
                        <p className="mt-1 text-xs text-slate-600">Recebera um alerta sempre que houver atualizacao.</p>
                        <div className="mt-3">
                            <CommentBox
                                storeRoute={route('portal.tickets.comments.store', ticket.id)}
                                defaultVisibility="public"
                                title="Acrescentar informacao"
                                submitLabel="Enviar mensagem"
                            />
                        </div>
                        <ul className="mt-3 space-y-3 text-sm">
                            {ticket.comments.map((comment) => (
                                <li key={comment.id} className="rounded-2xl border border-slate-100 bg-slate-50 p-3.5">
                                    <p className="text-slate-900">{comment.body}</p>
                                    <p className="mt-1 text-xs text-slate-600">{comment.user?.name ?? 'Sistema'} - {new Date(comment.created_at).toLocaleString()}</p>
                                </li>
                            ))}
                        </ul>
                    </AppCard>
                </div>

                <div className="space-y-4">
                    <AppCard>
                        <h3 className="text-base font-bold text-slate-900">Anexos publicos</h3>
                        <AttachmentUploader
                            storeRoute={route('portal.tickets.attachments.store', ticket.id)}
                            defaultVisibility="public"
                            title="Adicionar anexo"
                        />
                        <div className="mt-3">
                            <AttachmentList attachments={ticket.attachments} downloadRouteName="portal.attachments.download" />
                        </div>
                    </AppCard>
                </div>
            </div>
        </PortalLayout>
    );
}
