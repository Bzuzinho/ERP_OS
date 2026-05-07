<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\NotificationRecipient;
use App\Models\User;
use App\Services\Notifications\NotificationService;
use Illuminate\Database\Seeder;

class NotificationDemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@juntaos.local')->first();
        $cidadao = User::query()->where('email', 'cidadao@juntaos.local')->first();
        $associacao = User::query()->where('email', 'associacao@juntaos.local')->first();
        $operacional = User::query()->where('email', 'operacional@juntaos.local')->first();

        if (! $admin || ! $cidadao || ! $associacao || ! $operacional) {
            return;
        }

        $notificationService = app(NotificationService::class);

        $demoItems = [
            [
                'recipients' => [$admin],
                'type' => 'ticket_created',
                'title' => 'Novo pedido: Buraco na Rua Principal',
                'message' => 'Foi criado um pedido no portal e esta em tratamento.',
                'priority' => 'high',
                'action_url' => route('admin.tickets.index', [], false),
            ],
            [
                'recipients' => [$operacional],
                'type' => 'task_assigned',
                'title' => 'Nova tarefa atribuida: Verificar buraco na Rua Principal',
                'message' => 'Foi atribuida uma tarefa de manutencao associada ao pedido do municipe.',
                'priority' => 'high',
                'action_url' => route('admin.tasks.index', [], false),
            ],
            [
                'recipients' => [$cidadao],
                'type' => 'ticket_public_reply',
                'title' => 'Resposta da Junta no seu pedido',
                'message' => 'A Junta respondeu ao pedido Buraco na Rua Principal.',
                'priority' => 'normal',
                'action_url' => route('portal.tickets.index', [], false),
            ],
            [
                'recipients' => [$associacao],
                'type' => 'reservation_approved',
                'title' => 'Reserva aprovada: Salao Polivalente',
                'message' => 'O pedido de reserva foi aprovado e entrou na agenda.',
                'priority' => 'normal',
                'action_url' => route('portal.space-reservations.index', [], false),
            ],
            [
                'recipients' => [$admin],
                'type' => 'reservation_pending',
                'title' => 'Reserva pendente de aprovacao',
                'message' => 'Existe uma nova reserva de espaco a aguardar decisao.',
                'priority' => 'normal',
                'action_url' => route('admin.space-reservations.index', [], false),
            ],
            [
                'recipients' => [$admin],
                'type' => 'stock_low',
                'title' => 'Stock baixo de luvas descartaveis',
                'message' => 'O stock do item esta abaixo do minimo definido.',
                'priority' => 'high',
                'action_url' => route('admin.inventory-items.index', [], false),
            ],
            [
                'recipients' => [$admin],
                'type' => 'leave_request_pending',
                'title' => 'Pedido de ferias pendente',
                'message' => 'Um pedido de ferias necessita de aprovacao.',
                'priority' => 'normal',
                'action_url' => route('admin.hr.leave-requests.index', [], false),
            ],
            [
                'recipients' => [$admin],
                'type' => 'operational_plan_pending',
                'title' => 'Plano operacional pendente de aprovacao',
                'message' => 'Foi submetido um plano operacional para validacao.',
                'priority' => 'normal',
                'action_url' => route('admin.operational-plans.index', [], false),
            ],
        ];

        foreach ($demoItems as $item) {
            $recipientIds = collect($item['recipients'])->map(fn (User $user) => $user->id)->sort()->values()->all();

            $existing = Notification::query()
                ->where('organization_id', $admin->organization_id)
                ->where('created_by', $admin->id)
                ->where('type', $item['type'])
                ->where('title', $item['title'])
                ->where('action_url', $item['action_url'])
                ->with('recipients:user_id,notification_id')
                ->first();

            if (! $existing) {
                $notificationService->createForUsers($item['recipients'], [
                    'organization_id' => $admin->organization_id,
                    'type' => $item['type'],
                    'title' => $item['title'],
                    'message' => $item['message'],
                    'action_url' => $item['action_url'],
                    'priority' => $item['priority'],
                    'created_by' => $admin->id,
                ]);
                continue;
            }

            $existingRecipientIds = $existing->recipients->pluck('user_id')->sort()->values()->all();
            if ($existingRecipientIds !== $recipientIds) {
                NotificationRecipient::query()->where('notification_id', $existing->id)->delete();
                $notificationService->createForUsers($item['recipients'], [
                    'organization_id' => $admin->organization_id,
                    'type' => $item['type'],
                    'title' => $item['title'],
                    'message' => $item['message'],
                    'action_url' => $item['action_url'],
                    'priority' => $item['priority'],
                    'created_by' => $admin->id,
                ]);
            }
        }

        NotificationRecipient::query()
            ->whereHas('notification', fn ($query) => $query
                ->where('organization_id', $admin->organization_id)
                ->where('type', 'operational_plan_pending'))
            ->where('user_id', $admin->id)
            ->update(['read_at' => now()->subMinute()]);

        NotificationRecipient::query()
            ->whereHas('notification', fn ($query) => $query
                ->where('organization_id', $admin->organization_id)
                ->where('type', 'reservation_approved'))
            ->where('user_id', $associacao->id)
            ->update(['read_at' => now()->subMinutes(2)]);
    }
}
