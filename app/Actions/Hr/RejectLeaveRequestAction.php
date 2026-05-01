<?php

namespace App\Actions\Hr;

use App\Models\ActivityLog;
use App\Models\LeaveRequest;

class RejectLeaveRequestAction
{
    public function execute(LeaveRequest $request, array $data): LeaveRequest
    {
        $request->update([
            'status' => 'rejected',
            'rejected_by' => auth()->id(),
            'rejected_at' => now(),
            'rejection_reason' => $data['rejection_reason'] ?? null,
        ]);

        ActivityLog::create([
            'organization_id' => $request->organization_id,
            'user_id' => auth()->id(),
            'action' => 'updated',
            'subject_type' => LeaveRequest::class,
            'subject_id' => $request->id,
            'description' => "Pedido de ausência rejeitado para funcionário '{$request->employee->employee_number}'",
        ]);

        return $request;
    }
}
