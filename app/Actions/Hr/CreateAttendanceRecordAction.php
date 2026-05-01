<?php

namespace App\Actions\Hr;

use App\Models\ActivityLog;
use App\Models\AttendanceRecord;

class CreateAttendanceRecordAction
{
    public function execute(array $data): AttendanceRecord
    {
        $organizationId = $data['organization_id'] ?? auth()->user()->organization_id;

        // Calculate worked minutes if check_in and check_out exist
        if (isset($data['check_in']) && isset($data['check_out'])) {
            $checkIn = \Carbon\Carbon::parse($data['check_in']);
            $checkOut = \Carbon\Carbon::parse($data['check_out']);
            $breakMinutes = $data['break_minutes'] ?? 0;
            $data['worked_minutes'] = $checkIn->diffInMinutes($checkOut) - $breakMinutes;
        }

        $data['organization_id'] = $organizationId;
        $data['created_by'] = auth()->id();

        $record = AttendanceRecord::create($data);

        ActivityLog::create([
            'organization_id' => $organizationId,
            'user_id' => auth()->id(),
            'action' => 'created',
            'subject_type' => AttendanceRecord::class,
            'subject_id' => $record->id,
            'description' => "Presença registada para funcionário '{$record->employee->employee_number}' em {$record->date}",
        ]);

        return $record;
    }
}
