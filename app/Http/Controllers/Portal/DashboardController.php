<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the portal dashboard.
     */
    public function __invoke(): Response
    {
        return Inertia::render('Portal/Dashboard/Index', [
            'stats' => [
                ['label' => 'Pedidos ativos', 'value' => 3],
                ['label' => 'Respostas pendentes', 'value' => 1],
                ['label' => 'Próximas marcações', 'value' => 2],
                ['label' => 'Documentos disponíveis', 'value' => 5],
            ],
            'actions' => [
                'Novo pedido',
                'Consultar pedidos',
                'Pedir reserva',
            ],
        ]);
    }
}