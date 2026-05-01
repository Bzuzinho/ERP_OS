<?php

namespace App\Actions\Hr;

use App\Models\ActivityLog;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\DB;

class CancelLeaveRequestAction
{
    public function execute(LeaveRequest $request, array $data): LeaveRequest
    {
        return DB::transaction(function () use ($request, $data) {
            $request->update([
                'status' => 'cancelled',
                'cancelled_by' => auth()->id(),
                'cancelled_at' => now(),
                'cancellation_reason' => $data['cancellation_reason'] ?? null,
            ]);

            // Remove attendance records if they were auto-created and not validated
            if ($request->status === 'approved') {
                $request->employee->attendanceRecords()
                    ->whereBetween('date', [$request->start_date, $request->end_date])
                    ->where('source', 'system')
                    ->whereNull('validated_at')
                    ->delete();
            }

            ActivityLog::create([
                'organization_id' => $request->organization_id,
                'user_id' => auth()->id(),
                'action' => 'deleted',
                'subject_type' => LeaveRequest::class,
                'subject_id' => $request->id,
                'description' => "Pedido de ausência cancelado para funcionário '{$request->employee->employee_number}'",
            ]);

            return $request;
        });
    }
}
