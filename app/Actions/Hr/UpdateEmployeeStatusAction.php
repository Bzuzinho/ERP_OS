<?php

namespace App\Actions\Hr;

use App\Models\ActivityLog;
use App\Models\Employee;

class UpdateEmployeeStatusAction
{
    public function execute(Employee $employee, array $data): Employee
    {
        $changes = [];

        if (isset($data['is_active']) && $data['is_active'] !== $employee->is_active) {
            $changes['is_active'] = $data['is_active'];
        }

        if (isset($data['end_date']) && $data['end_date'] !== $employee->end_date) {
            $changes['end_date'] = $data['end_date'];
        }

        if (!empty($changes)) {
            $employee->update($changes);

            $description = "Funcionário '{$employee->employee_number}' atualizado";
            if ($changes['is_active'] ?? null === false) {
                $description .= ' - desativado';
            } elseif ($changes['is_active'] ?? null === true) {
                $description .= ' - reativado';
            }

            ActivityLog::create([
                'organization_id' => $employee->organization_id,
                'user_id' => auth()->id(),
                'action' => 'updated',
                'subject_type' => Employee::class,
                'subject_id' => $employee->id,
                'description' => $description,
            ]);
        }

        return $employee;
    }
}
