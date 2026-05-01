<?php

namespace App\Actions\Hr;

use App\Models\ActivityLog;
use App\Models\LeaveRequest;
use App\Services\LeaveRequestService;

class CreateLeaveRequestAction
{
    public function execute(array $data): LeaveRequest
    {
        $organizationId = $data['organization_id'] ?? auth()->user()->organization_id;

        // Calculate total days
        $startDate = \Carbon\Carbon::parse($data['start_date']);
        $endDate = \Carbon\Carbon::parse($data['end_date']);
        $totalDays = $endDate->diffInDays($startDate) + 1;

        $data['organization_id'] = $organizationId;
        $data['created_by'] = auth()->id();
        $data['total_days'] = $totalDays;

        // Validate overlapping requests
        $service = new LeaveRequestService();
        $service->validateNoOverlap($data['employee_id'], $startDate, $endDate);

        $request = LeaveRequest::create($data);

        ActivityLog::create([
            'organization_id' => $organizationId,
            'user_id' => auth()->id(),
            'action' => 'created',
            'subject_type' => LeaveRequest::class,
            'subject_id' => $request->id,
            'description' => "Pedido de ausência criado para funcionário '{$request->employee->employee_number}' de {$startDate->format('d/m/Y')} a {$endDate->format('d/m/Y')}",
        ]);

        return $request;
    }
}
