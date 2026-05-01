<?php

namespace App\Services;

use App\Models\Employee;

class EmployeeNumberGenerator
{
    public function generate(int $organizationId): string
    {
        $year = now()->year;
        $count = Employee::where('organization_id', $organizationId)
            ->whereYear('created_at', $year)
            ->count() + 1;

        return sprintf('EMP-%d-%04d', $year, $count);
    }
}
