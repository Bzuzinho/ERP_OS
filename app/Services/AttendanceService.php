<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AttendanceService
{
    public function calculateWorkedMinutes(string $checkIn, string $checkOut, int $breakMinutes = 0): int
    {
        $in = Carbon::parse($checkIn);
        $out = Carbon::parse($checkOut);
        
        return max(0, $in->diffInMinutes($out) - $breakMinutes);
    }

    public function getAttendanceMapByPeriod(Employee $employee, Carbon $startDate, Carbon $endDate): array
    {
        $records = $employee->attendanceRecords()
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get();

        $map = [];
        foreach (CarbonPeriod::create($startDate, $endDate) as $date) {
            $record = $records->firstWhere('date', $date->toDateString());
            $map[$date->toDateString()] = [
                'date' => $date->toDateString(),
                'status' => $record?->status ?? 'off',
                'check_in' => $record?->check_in,
                'check_out' => $record?->check_out,
                'worked_minutes' => $record?->worked_minutes,
                'validated' => !is_null($record?->validated_at),
            ];
        }

        return $map;
    }

    public function getAbsencesByPeriod(Employee $employee, Carbon $startDate, Carbon $endDate): array
    {
        return $employee->attendanceRecords()
            ->whereBetween('date', [$startDate, $endDate])
            ->whereIn('status', ['absent', 'vacation', 'sick_leave', 'justified_absence', 'unjustified_absence'])
            ->orderBy('date')
            ->get()
            ->toArray();
    }
}
