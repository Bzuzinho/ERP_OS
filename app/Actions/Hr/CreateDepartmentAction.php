<?php

namespace App\Actions\Hr;

use App\Models\ActivityLog;
use App\Models\Department;

class CreateDepartmentAction
{
    public function execute(array $data): Department
    {
        $department = Department::create($data);

        ActivityLog::create([
            'organization_id' => $data['organization_id'] ?? auth()->user()->organization_id,
            'user_id' => auth()->id(),
            'action' => 'created',
            'subject_type' => Department::class,
            'subject_id' => $department->id,
            'description' => "Departamento '{$department->name}' criado",
        ]);

        return $department;
    }
}
