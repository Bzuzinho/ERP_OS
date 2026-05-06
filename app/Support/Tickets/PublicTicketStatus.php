<?php

namespace App\Support\Tickets;

class PublicTicketStatus
{
    /**
     * @var array<string, string>
     */
    private const LABEL_BY_INTERNAL_STATUS = [
        'new' => 'Recebido',
        'aberto' => 'Recebido',
        'novo' => 'Recebido',
        'in_analysis' => 'Em analise',
        'em_analise' => 'Em analise',
        'assigned' => 'Em tratamento',
        'encaminhado' => 'Em tratamento',
        'in_progress' => 'Em tratamento',
        'em_execucao' => 'Em tratamento',
        'agendado' => 'Em tratamento',
        'waiting_info' => 'A aguardar informacao',
        'aguarda_informacao' => 'A aguardar informacao',
        'resolved' => 'Resolvido',
        'resolvido' => 'Resolvido',
        'closed' => 'Encerrado',
        'fechado' => 'Encerrado',
        'cancelled' => 'Cancelado',
        'cancelado' => 'Cancelado',
    ];

    /**
     * @var string[]
     */
    private const CITIZEN_RELEVANT_LABELS = [
        'Recebido',
        'Em analise',
        'Em tratamento',
        'A aguardar informacao',
        'Resolvido',
        'Encerrado',
        'Cancelado',
    ];

    public static function label(string $internalStatus): string
    {
        $normalized = mb_strtolower(trim($internalStatus));

        return self::LABEL_BY_INTERNAL_STATUS[$normalized] ?? 'Em analise';
    }

    public static function shouldNotifyCitizenForTransition(string $oldStatus, string $newStatus): bool
    {
        $oldLabel = self::label($oldStatus);
        $newLabel = self::label($newStatus);

        if ($oldLabel === $newLabel) {
            return false;
        }

        return in_array($newLabel, self::CITIZEN_RELEVANT_LABELS, true);
    }
}
