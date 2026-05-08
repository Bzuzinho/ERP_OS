<?php

namespace App\Services\Tickets;

use App\Models\Ticket;
use App\Models\User;
use App\Services\Notifications\TicketNotificationService;

class TicketResolutionService
{
    public function __construct(
        private readonly TicketStateTransitionService $stateTransitionService,
        private readonly TicketNotificationService $ticketNotificationService,
    ) {
    }

    /**
     * @return array{
     *     total:int,
     *     eligible:int,
     *     done:int,
     *     cancelled:int,
     *     open:int,
     *     in_progress:int,
     *     ready_for_validation:bool
     * }
     */
    public function progress(Ticket $ticket): array
    {
        $tasks = $ticket->tasks()
            ->select('id', 'status')
            ->get();

        $eligibleTasks = $tasks->where('status', '!=', 'cancelled')->values();
        $eligibleCount = $eligibleTasks->count();

        $usesValidationWorkflow = $eligibleTasks
            ->whereIn('status', ['pending_validation', 'validated', 'reopened'])
            ->isNotEmpty();

        $completedCount = $usesValidationWorkflow
            ? $eligibleTasks->where('status', 'validated')->count()
            : $eligibleTasks->where('status', 'done')->count();

        $openCount = $eligibleTasks->count() - $completedCount;

        $inProgressCount = $eligibleTasks->whereIn('status', ['in_progress', 'reopened'])->count();

        return [
            'total' => $tasks->count(),
            'eligible' => $eligibleCount,
            'done' => $completedCount,
            'cancelled' => $tasks->where('status', 'cancelled')->count(),
            'open' => $openCount,
            'in_progress' => $inProgressCount,
            'ready_for_validation' => $eligibleCount > 0 && $completedCount === $eligibleCount,
        ];
    }

    public function canSubmitForValidation(Ticket $ticket): bool
    {
        if (in_array($ticket->status, ['cancelado', 'fechado', 'indeferido', 'resolvido'], true)) {
            return false;
        }

        return $this->progress($ticket)['ready_for_validation'];
    }

    public function syncFromTasks(Ticket $ticket, User $actor, ?string $notes = null): Ticket
    {
        $ticket = $ticket->fresh() ?? $ticket;
        $progress = $this->progress($ticket);

        if ($progress['total'] === 0) {
            return $ticket;
        }

        if ($progress['ready_for_validation']) {
            return $ticket;
        }

        $targetStatus = $progress['in_progress'] > 0 ? 'em_execucao' : 'com_tarefas';
        $oldStatus = $ticket->status;

        if ($oldStatus === $targetStatus) {
            return $ticket;
        }

        $attributes = [];

        if (in_array($oldStatus, ['aguarda_validacao', 'resolvido'], true)) {
            $attributes['validated_at'] = null;
            $attributes['validated_by'] = null;
            $attributes['validation_notes'] = null;
        }

        $updatedTicket = $this->stateTransitionService->transition(
            ticket: $ticket,
            newStatus: $targetStatus,
            changedBy: $actor,
            notes: $notes,
            attributes: $attributes,
        )->fresh();

        if (in_array($oldStatus, ['aguarda_validacao', 'resolvido'], true)) {
            $this->ticketNotificationService->notifyTicketReopenedFromTasks($updatedTicket, $actor);
        }

        return $updatedTicket;
    }
}