<?php

namespace App\Actions\Hr;

use App\Models\ActivityLog;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;

class CreateEmployeeAction
{
    public function execute(array $data): Employee
    {
        $organizationId = $data['organization_id'] ?? auth()->user()->organization_id;

        // Generate employee number if not provided
        if (empty($data['employee_number'])) {
            $data['employee_number'] = $this->generateEmployeeNumber($organizationId);
        }

        $employee = Employee::create([
            ...$data,
            'organization_id' => $organizationId,
        ]);

        ActivityLog::create([
            'organization_id' => $organizationId,
            'user_id' => auth()->id(),
            'action' => 'created',
            'subject_type' => Employee::class,
            'subject_id' => $employee->id,
            'description' => "Funcionário '{$employee->employee_number}' criado",
        ]);

        return $employee;
    }

    private function generateEmployeeNumber(int $organizationId): string
    {
        $year = now()->year;
        $count = Employee::where('organization_id', $organizationId)
            ->whereYear('created_at', $year)
            ->count() + 1;

        return sprintf('EMP-%d-%04d', $year, $count);
    }
}
