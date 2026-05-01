<?php

namespace App\Actions\Hr;

use App\Models\ActivityLog;
use App\Models\AttendanceRecord;

class ValidateAttendanceRecordAction
{
    public function execute(AttendanceRecord $record): AttendanceRecord
    {
        $record->update([
            'validated_by' => auth()->id(),
            'validated_at' => now(),
        ]);

        ActivityLog::create([
            'organization_id' => $record->organization_id,
            'user_id' => auth()->id(),
            'action' => 'updated',
            'subject_type' => AttendanceRecord::class,
            'subject_id' => $record->id,
            'description' => "Presença validada para funcionário '{$record->employee->employee_number}' em {$record->date}",
        ]);

        return $record;
    }
}
