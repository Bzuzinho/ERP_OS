<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function __invoke(): Response
    {
        return Inertia::render('Admin/Dashboard/Index', [
            'stats' => [
                ['label' => 'Pedidos em aberto', 'value' => 128],
                ['label' => 'Urgentes', 'value' => 8],
                ['label' => 'Reservas hoje', 'value' => 7],
                ['label' => 'Stock baixo', 'value' => 12],
                ['label' => 'Ausências', 'value' => 6],
                ['label' => 'Atividades planeadas', 'value' => 13],
            ],
            'sections' => [
                [
                    'title' => 'Pedidos Recentes',
                    'description' => 'Espaço reservado para a fila operacional e priorização de pedidos.',
                ],
                [
                    'title' => 'Agenda de Hoje',
                    'description' => 'Resumo futuro de marcações, deslocações e compromissos da equipa.',
                ],
                [
                    'title' => 'Alertas',
                    'description' => 'Zona de incidentes, SLA e avisos de operação críticos.',
                ],
            ],
        ]);
    }
}