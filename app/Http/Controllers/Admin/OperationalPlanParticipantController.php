<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OperationalPlan;
use App\Models\OperationalPlanParticipant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OperationalPlanParticipantController extends Controller
{
    public function store(Request $request, OperationalPlan $operationalPlan): RedirectResponse
    {
        $this->authorize('update', $operationalPlan);

        $data = $request->validate([
            'user_id' => ['nullable', 'exists:users,id'],
            'employee_id' => ['nullable', 'exists:employees,id'],
            'team_id' => ['nullable', 'exists:teams,id'],
            'role' => ['nullable', 'string', 'max:255'],
        ]);

        $operationalPlan->participants()->create($data);

        return back()->with('success', 'Participante adicionado ao plano.');
    }

    public function destroy(OperationalPlan $operationalPlan, OperationalPlanParticipant $participant): RedirectResponse
    {
        $this->authorize('update', $operationalPlan);

        if ((int) $participant->operational_plan_id === (int) $operationalPlan->id) {
            $participant->delete();
        }

        return back()->with('success', 'Participante removido do plano.');
    }
}
