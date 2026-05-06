<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\Notifications\NotificationService;
use Illuminate\Database\Seeder;

class NotificationDemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@juntaos.local')->first();
        if (! $admin) {
            return;
        }

        $notificationService = app(NotificationService::class);

        $demoItems = [
            [
                'type' => 'ticket_created',
                'title' => 'Novo pedido de buraco na via publica',
                'message' => 'Foi reportado um buraco junto ao largo principal.',
                'priority' => 'high',
                'action_url' => route('admin.tickets.index', [], false),
            ],
            [
                'type' => 'reservation_pending',
                'title' => 'Reserva pendente de aprovacao',
                'message' => 'Existe uma nova reserva de espaco a aguardar decisao.',
                'priority' => 'normal',
                'action_url' => route('admin.space-reservations.index', [], false),
            ],
            [
                'type' => 'stock_low',
                'title' => 'Stock baixo de luvas descartaveis',
                'message' => 'O stock do item esta abaixo do minimo definido.',
                'priority' => 'high',
                'action_url' => route('admin.inventory-items.index', [], false),
            ],
            [
                'type' => 'leave_request_pending',
                'title' => 'Pedido de ferias pendente',
                'message' => 'Um pedido de ferias necessita de aprovacao.',
                'priority' => 'normal',
                'action_url' => route('admin.hr.leave-requests.index', [], false),
            ],
            [
                'type' => 'operational_plan_pending',
                'title' => 'Plano operacional pendente de aprovacao',
                'message' => 'Foi submetido um plano operacional para validacao.',
                'priority' => 'normal',
                'action_url' => route('admin.operational-plans.index', [], false),
            ],
        ];

        foreach ($demoItems as $item) {
            $notificationService->createForUsers([$admin], [
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
}
