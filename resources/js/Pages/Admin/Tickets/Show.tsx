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
    priority: string;
    source: string;
    type: string;
    due_date: string | null;
    created_by: number;
    created_at?: string | null;
    visibility: string;
    organization?: { id: number; name: string } | null;
    assignee: UserRef | null;
    validator?: UserRef | null;
    validated_at?: string | null;
    validation_notes?: string | null;
    resolution_notes?: string | null;
    service_area?: { id: number; name: string } | null;
    department?: { id: number; name: string } | null;
    team?: { id: number; name: string } | null;
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
        visibility: 'internal' | 'public';
        created_at: string;
        user: UserRef | null;
    }>;
    attachments: Array<{
        id: number;
        file_name: string;
        file_path: string;
        visibility: 'internal' | 'public';
        uploaded_by: number | null;
        uploader?: UserRef | null;
        created_at?: string | null;
    }>;
    activity_logs?: Array<{
        id: number;
        action: string;
        description: string | null;
        created_at: string;
        user: UserRef | null;
    }>;
    tasks?: Array<{
        id: number;
        title: string;
        status: string;
    }>;
};

type Props = {
    ticket: Ticket;
    statuses: string[];
    users: UserRef[];
    can: {
        submitValidation: boolean;
        validate: boolean;
        cancel: boolean;
        generateTasks: boolean;
    };
    ticketAbilities: {
        can_be_validated: boolean;
        can_be_cancelled: boolean;
        can_generate_tasks: boolean;
        can_submit_for_validation: boolean;
    };
    taskProgress: {
        total: number;
        eligible: number;
        done: number;
        cancelled: number;
        open: number;
        in_progress: number;
        ready_for_validation: boolean;
    };
};

export default function TicketsShow({ ticket, statuses, users, can, ticketAbilities, taskProgress }: Props) {
    const statusForm = useForm({ status: ticket.status, notes: '' });
    const assignForm = useForm({ assigned_to: ticket.assignee?.id ? String(ticket.assignee.id) : '' });
    const submitValidationForm = useForm({ notes: '', resolution_notes: ticket.resolution_notes ?? '' });
    const validateForm = useForm({ validation_notes: '', resolution_notes: '' });
    const cancelForm = useForm({ notes: '' });
    const generateTaskForm = useForm({
        tasks: [{
            title: `Tarefa de ${ticket.reference}`,
            description: ticket.title,
            priority: 'normal',
        }],
    });

    const submitStatus = (event: FormEvent) => {
        event.preventDefault();
        statusForm.patch(route('admin.tickets.status.update', ticket.id));
    };

    const submitAssign = (event: FormEvent) => {
        event.preventDefault();
        assignForm.patch(route('admin.tickets.assign', ticket.id));
    };

    const submitValidate = (event: FormEvent) => {
        event.preventDefault();
        validateForm.post(route('admin.tickets.validate', ticket.id));
    };

    const submitForValidation = (event: FormEvent) => {
        event.preventDefault();
        submitValidationForm.post(route('admin.tickets.submit-validation', ticket.id));
    };

    const submitCancel = (event: FormEvent) => {
        event.preventDefault();
        cancelForm.post(route('admin.tickets.cancel', ticket.id));
    };

    const submitGenerateTask = (event: FormEvent) => {
        event.preventDefault();
        generateTaskForm.post(route('admin.tickets.generate-tasks', ticket.id));
    };

    const statusTone = (status: string): 'blue' | 'amber' | 'green' | 'red' | 'slate' => {
        const normalized = status.toLowerCase();
        if (normalized.includes('fech') || normalized.includes('resol')) return 'green';
        if (normalized.includes('cancel')) return 'red';
        if (normalized.includes('pend') || normalized.includes('anal') || normalized.includes('aguarda')) return 'amber';
        if (normalized.includes('novo') || normalized.includes('encaminh') || normalized.includes('exec')) return 'blue';
        return 'slate';
    };

    const priorityTone = (priority: string): 'blue' | 'amber' | 'green' | 'red' | 'slate' => {
        const normalized = priority.toLowerCase();
        if (normalized.includes('high') || normalized.includes('alta') || normalized.includes('urgent')) return 'red';
        if (normalized.includes('normal') || normalized.includes('media')) return 'amber';
        if (normalized.includes('low') || normalized.includes('baixa')) return 'green';
        return 'slate';
    };

    const communicationComments = ticket.comments.filter((comment) => comment.visibility === 'public');
    const internalNotes = ticket.comments.filter((comment) => comment.visibility === 'internal');
    const progressPercent = taskProgress.eligible > 0 ? Math.round((taskProgress.done / taskProgress.eligible) * 100) : 0;

    const timelineEntries: TimelineEntry[] = [
        ...(ticket.created_at
            ? [{
                id: `created-${ticket.id}`,
                kind: 'created' as const,
                title: 'Pedido criado',
                by: ticket.contact?.name ? `Contacto ${ticket.contact.name}` : 'Sistema',
                at: ticket.created_at,
                description: ticket.description,
            }]
            : []),
        ...ticket.status_histories.map((entry) => ({
            id: `status-${entry.id}`,
            kind: 'status' as const,
            title: entry.old_status ? `Estado alterado: ${entry.old_status} -> ${entry.new_status}` : `Estado inicial: ${entry.new_status}`,
            description: entry.notes,
            by: entry.changed_by?.name ?? 'Sistema',
            at: entry.created_at,
        })),
        ...ticket.comments.map((comment) => ({
            id: `comment-${comment.id}`,
            kind: 'comment' as const,
            title: comment.visibility === 'internal'
                ? 'Nota interna'
                : (comment.user?.id === ticket.created_by ? 'Mensagem do municipe' : 'Resposta da Junta'),
            description: comment.body,
            by: comment.user?.name ?? 'Sistema',
            at: comment.created_at,
            visibility: comment.visibility,
        })),
        ...ticket.attachments.map((attachment) => ({
            id: `attachment-${attachment.id}`,
            kind: 'attachment' as const,
            title: `Anexo: ${attachment.file_name}`,
            by: attachment.uploader?.name ?? 'Sistema',
            at: attachment.created_at ?? ticket.created_at ?? new Date().toISOString(),
            visibility: attachment.visibility,
        })),
        ...(ticket.activity_logs ?? []).map((log) => ({
            id: `activity-${log.id}`,
            kind: log.action === 'ticket.assigned' ? 'assignment' as const : 'activity' as const,
            title: log.action === 'ticket.assigned' ? 'Atribuicao atualizada' : 'Evento tecnico',
            description: log.description,
            by: log.user?.name ?? 'Sistema',
            at: log.created_at,
        })),
    ];

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
                    <div className="mt-3 flex flex-wrap items-center gap-2">
                        <AppBadge tone={statusTone(ticket.status)}>{ticket.status}</AppBadge>
                        <AppBadge tone={priorityTone(ticket.priority)}>{ticket.priority}</AppBadge>
                    </div>
                    <div className="mt-3 grid gap-2 text-sm text-slate-600 sm:grid-cols-2">
                        <p><span className="font-semibold text-slate-900">Tipo:</span> {ticket.type}</p>
                        <p><span className="font-semibold text-slate-900">Organização:</span> {ticket.organization?.name ?? 'Nao definida'}</p>
                        <p><span className="font-semibold text-slate-900">Responsavel:</span> {ticket.assignee?.name ?? 'Por atribuir'}</p>
                        <p><span className="font-semibold text-slate-900">Tema:</span> {ticket.service_area?.name ?? 'Nao definida'}</p>
                        <p><span className="font-semibold text-slate-900">Departamento:</span> {ticket.department?.name ?? 'Nao definido'}</p>
                        <p><span className="font-semibold text-slate-900">Equipa:</span> {ticket.team?.name ?? 'Nao definida'}</p>
                    </div>
                    <div className="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-3 text-sm text-slate-700">
                        <p><span className="font-semibold text-slate-900">Validação:</span> {ticket.validated_at ? `Validado por ${ticket.validator?.name ?? 'utilizador'} em ${new Date(ticket.validated_at).toLocaleString()}` : 'Pendente'}</p>
                        {ticket.validation_notes ? <p className="mt-1"><span className="font-semibold text-slate-900">Notas validação:</span> {ticket.validation_notes}</p> : null}
                        {ticket.resolution_notes ? <p className="mt-1"><span className="font-semibold text-slate-900">Notas resolução:</span> {ticket.resolution_notes}</p> : null}
                    </div>
                </AppCard>

                <ContactSummaryCard contact={ticket.contact} />
            </div>

            <AppCard className="mt-4">
                <h3 className="text-base font-bold text-slate-900">Descricao</h3>
                <p className="mt-3 text-sm leading-6 text-slate-700">{ticket.description}</p>
            </AppCard>

            <AppCard className="mt-4">
                <h3 className="text-base font-bold text-slate-900">Acoes rapidas</h3>
                <div className="mt-3 flex flex-wrap gap-2 text-sm">
                    <a href="#bloco-estado" className="rounded-xl border border-slate-300 px-3 py-1.5 font-semibold text-slate-700 hover:bg-slate-100">Mudar estado</a>
                    <a href="#bloco-atribuicao" className="rounded-xl border border-slate-300 px-3 py-1.5 font-semibold text-slate-700 hover:bg-slate-100">Atribuir responsavel</a>
                    <a href="#bloco-comunicacao" className="rounded-xl border border-blue-300 bg-blue-50 px-3 py-1.5 font-semibold text-blue-700 hover:bg-blue-100">Responder ao municipe</a>
                    <a href="#bloco-notas" className="rounded-xl border border-amber-300 bg-amber-50 px-3 py-1.5 font-semibold text-amber-700 hover:bg-amber-100">Adicionar nota interna</a>
                    <a href="#bloco-anexos" className="rounded-xl border border-slate-300 px-3 py-1.5 font-semibold text-slate-700 hover:bg-slate-100">Anexar ficheiro</a>
                </div>
            </AppCard>

            <AppCard className="mt-4">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h3 className="text-base font-bold text-slate-900">Progresso operacional</h3>
                        <p className="mt-1 text-sm text-slate-600">
                            {taskProgress.total === 0
                                ? 'Este pedido ainda não tem tarefas associadas.'
                                : `${taskProgress.done}/${taskProgress.eligible || taskProgress.total} tarefas elegíveis concluídas`}
                        </p>
                    </div>
                    {taskProgress.total > 0 ? <AppBadge tone={taskProgress.ready_for_validation ? 'green' : 'blue'}>{progressPercent}%</AppBadge> : null}
                </div>
                {taskProgress.total > 0 ? (
                    <>
                        <div className="mt-3 h-3 overflow-hidden rounded-full bg-slate-200">
                            <div className={`h-full rounded-full ${taskProgress.ready_for_validation ? 'bg-emerald-500' : 'bg-blue-500'}`} style={{ width: `${progressPercent}%` }} />
                        </div>
                        <div className="mt-3 grid gap-2 text-sm text-slate-600 sm:grid-cols-4">
                            <p><span className="font-semibold text-slate-900">Totais:</span> {taskProgress.total}</p>
                            <p><span className="font-semibold text-slate-900">Concluídas:</span> {taskProgress.done}</p>
                            <p><span className="font-semibold text-slate-900">Abertas:</span> {taskProgress.open}</p>
                            <p><span className="font-semibold text-slate-900">Canceladas:</span> {taskProgress.cancelled}</p>
                        </div>
                    </>
                ) : null}
                {ticketAbilities.can_submit_for_validation ? (
                    <div className="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                        <p className="text-sm font-semibold text-emerald-900">Todas as tarefas estão concluídas. Pedido pronto para validação.</p>
                        {can.submitValidation ? (
                            <form onSubmit={submitForValidation} className="mt-3">
                                <textarea value={submitValidationForm.data.resolution_notes} onChange={(event) => submitValidationForm.setData('resolution_notes', event.target.value)} placeholder="Notas finais de resolução" className="min-h-20 w-full rounded-2xl border border-emerald-300 px-3 py-2.5 text-sm" />
                                <button type="submit" className="mt-3 rounded-2xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Enviar para validação</button>
                            </form>
                        ) : null}
                    </div>
                ) : null}
            </AppCard>

            <div className="mt-4 grid gap-4 lg:grid-cols-2">
                <form id="bloco-estado" onSubmit={submitStatus} className="rounded-3xl border border-slate-200/70 bg-white p-5 shadow-sm">
                    <h3 className="text-lg font-semibold text-slate-900">Mudar estado</h3>
                    <div className="mt-3 flex flex-col gap-3">
                        <select value={statusForm.data.status} onChange={(event) => statusForm.setData('status', event.target.value)} className="rounded-2xl border border-slate-300 px-3 py-2.5 text-sm">
                            {statuses.map((status) => <option key={status} value={status}>{status}</option>)}
                        </select>
                        <textarea value={statusForm.data.notes} onChange={(event) => statusForm.setData('notes', event.target.value)} placeholder="Notas da atualizacao" className="min-h-24 rounded-2xl border border-slate-300 px-3 py-2.5 text-sm" />
                        <button type="submit" className="rounded-2xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Atualizar estado</button>
                    </div>
                </form>

                <form id="bloco-atribuicao" onSubmit={submitAssign} className="rounded-3xl border border-slate-200/70 bg-white p-5 shadow-sm">
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

            <div className="mt-4 grid gap-4 lg:grid-cols-3">
                {can.validate && ticketAbilities.can_be_validated ? (
                    <form onSubmit={submitValidate} className="rounded-3xl border border-emerald-200 bg-emerald-50/40 p-5">
                        <h3 className="text-base font-semibold text-emerald-900">Validar pedido</h3>
                        <textarea value={validateForm.data.validation_notes} onChange={(event) => validateForm.setData('validation_notes', event.target.value)} placeholder="Notas de validação" className="mt-3 min-h-20 w-full rounded-2xl border border-emerald-300 px-3 py-2.5 text-sm" />
                        <textarea value={validateForm.data.resolution_notes} onChange={(event) => validateForm.setData('resolution_notes', event.target.value)} placeholder="Notas de resolução" className="mt-2 min-h-20 w-full rounded-2xl border border-emerald-300 px-3 py-2.5 text-sm" />
                        <button type="submit" className="mt-3 rounded-2xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Validar</button>
                    </form>
                ) : null}

                {can.cancel && ticketAbilities.can_be_cancelled ? (
                    <form onSubmit={submitCancel} className="rounded-3xl border border-rose-200 bg-rose-50/40 p-5">
                        <h3 className="text-base font-semibold text-rose-900">Cancelar pedido</h3>
                        <textarea value={cancelForm.data.notes} onChange={(event) => cancelForm.setData('notes', event.target.value)} placeholder="Motivo do cancelamento" className="mt-3 min-h-20 w-full rounded-2xl border border-rose-300 px-3 py-2.5 text-sm" />
                        <button type="submit" className="mt-3 rounded-2xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-rose-700">Cancelar</button>
                    </form>
                ) : null}

                {can.generateTasks && ticketAbilities.can_generate_tasks ? (
                    <form onSubmit={submitGenerateTask} className="rounded-3xl border border-blue-200 bg-blue-50/40 p-5">
                        <h3 className="text-base font-semibold text-blue-900">Criar tarefa</h3>
                        <input value={generateTaskForm.data.tasks[0].title} onChange={(event) => generateTaskForm.setData('tasks', [{ ...generateTaskForm.data.tasks[0], title: event.target.value }])} placeholder="Título da tarefa" className="mt-3 w-full rounded-2xl border border-blue-300 px-3 py-2.5 text-sm" />
                        <textarea value={generateTaskForm.data.tasks[0].description} onChange={(event) => generateTaskForm.setData('tasks', [{ ...generateTaskForm.data.tasks[0], description: event.target.value }])} placeholder="Descrição" className="mt-2 min-h-20 w-full rounded-2xl border border-blue-300 px-3 py-2.5 text-sm" />
                        <button type="submit" className="mt-3 rounded-2xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">Gerar tarefa</button>
                    </form>
                ) : null}
            </div>

            <div className="mt-4 grid gap-4 lg:grid-cols-2">
                <section id="bloco-comunicacao" className="space-y-4 rounded-3xl border border-blue-200 bg-blue-50/40 p-4">
                    <CommentBox
                        storeRoute={route('admin.tickets.comments.store', ticket.id)}
                        canSetVisibility
                        defaultVisibility="public"
                        title="Comunicacao com o municipe"
                        helperText="Escolha Resposta ao municipe para enviar atualizacao visivel no Portal."
                        submitLabel="Enviar resposta"
                    />
                    <AppCard>
                        <h3 className="text-base font-bold text-slate-900">Comunicacao</h3>
                        <ul className="mt-3 space-y-3 text-sm">
                            {communicationComments.length ? communicationComments.map((comment) => (
                                <li key={comment.id} className="rounded-2xl border border-blue-100 bg-white p-3.5">
                                    <div className="mb-1 inline-flex rounded-full bg-blue-100 px-2 py-0.5 text-[11px] font-semibold text-blue-700">Visivel ao municipe</div>
                                    <p className="text-slate-900">{comment.body}</p>
                                    <p className="mt-1 text-xs text-slate-600">{comment.user?.name ?? 'Sistema'} - {new Date(comment.created_at).toLocaleString()}</p>
                                </li>
                            )) : <li className="text-sm text-slate-600">Sem respostas publicas ainda.</li>}
                        </ul>
                    </AppCard>
                </section>

                <section id="bloco-notas" className="space-y-4 rounded-3xl border border-amber-200 bg-amber-50/40 p-4">
                    <CommentBox
                        storeRoute={route('admin.tickets.comments.store', ticket.id)}
                        canSetVisibility
                        defaultVisibility="internal"
                        title="Notas internas"
                        helperText="Notas internas nunca aparecem no Portal do municipe."
                        submitLabel="Guardar nota"
                    />
                    <AppCard>
                        <h3 className="text-base font-bold text-slate-900">Notas internas</h3>
                        <ul className="mt-3 space-y-3 text-sm">
                            {internalNotes.length ? internalNotes.map((comment) => (
                                <li key={comment.id} className="rounded-2xl border border-amber-100 bg-white p-3.5">
                                    <div className="mb-1 inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-700">Interno</div>
                                    <p className="text-slate-900">{comment.body}</p>
                                    <p className="mt-1 text-xs text-slate-600">{comment.user?.name ?? 'Sistema'} - {new Date(comment.created_at).toLocaleString()}</p>
                                </li>
                            )) : <li className="text-sm text-slate-600">Sem notas internas.</li>}
                        </ul>
                    </AppCard>
                </section>
            </div>

            <section id="bloco-anexos" className="mt-4 rounded-3xl border border-slate-200/70 bg-white p-5 shadow-sm">
                <h3 className="text-lg font-semibold text-slate-900">Anexos</h3>
                <AttachmentUploader storeRoute={route('admin.tickets.attachments.store', ticket.id)} canSetVisibility defaultVisibility="internal" />
                <AttachmentList attachments={ticket.attachments} downloadRouteName="admin.attachments.download" />
            </section>

            <section className="mt-4 rounded-3xl border border-slate-200/70 bg-white p-5 shadow-sm">
                <TicketTimeline
                    mode="admin"
                    title="Historico"
                    entries={timelineEntries}
                    emptyText="Sem historico de comunicacao para este pedido."
                />
            </section>
        </AdminLayout>
    );
}
