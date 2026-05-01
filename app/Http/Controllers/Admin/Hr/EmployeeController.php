<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Actions\Hr\CreateEmployeeAction;
use App\Actions\Hr\UpdateEmployeeStatusAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Hr\StoreEmployeeRequest;
use App\Http\Requests\Hr\UpdateEmployeeRequest;
use App\Http\Requests\Hr\UpdateEmployeeStatusRequest;
use App\Models\Employee;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeController extends Controller
{
    public function __construct(
        private readonly CreateEmployeeAction $createAction,
        private readonly UpdateEmployeeStatusAction $updateStatusAction,
    ) {}

    public function index(): Response
    {
        $this->authorize('viewAny', Employee::class);

        $employees = Employee::where('organization_id', auth()->user()->organization_id)
            ->with('user', 'department', 'teams')
            ->when(request('department'), fn($q) => $q->where('department_id', request('department')))
            ->when(request('employment_type'), fn($q) => $q->where('employment_type', request('employment_type')))
            ->when(request('status'), fn($q) => $q->where('is_active', request('status') === 'active'))
            ->when(request('search'), fn($q) => $q->whereHas('user', fn($u) => $u->where('name', 'like', '%' . request('search') . '%'))
                ->orWhere('employee_number', 'like', '%' . request('search') . '%'))
            ->orderBy('employee_number')
            ->paginate(15);

        $departments = Department::where('organization_id', auth()->user()->organization_id)
            ->pluck('name', 'id');

        return Inertia::render('Admin/Employees/Index', [
            'employees' => $employees,
            'departments' => $departments,
            'filters' => request()->only(['department', 'employment_type', 'status', 'search']),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Employee::class);

        $users = User::where('organization_id', auth()->user()->organization_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        $departments = Department::where('organization_id', auth()->user()->organization_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Admin/Employees/Create', [
            'users' => $users,
            'departments' => $departments,
            'employmentTypes' => Employee::EMPLOYMENT_TYPES,
        ]);
    }

    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        $this->authorize('create', Employee::class);

        $data = $request->validated();
        $data['organization_id'] = auth()->user()->organization_id;

        $this->createAction->execute($data);

        return redirect()->route('admin.hr.employees.index')
            ->with('success', 'Funcionário criado com sucesso!');
    }

    public function show(Employee $employee): Response
    {
        $this->authorize('view', $employee);

        $employee->load('user', 'department', 'teams', 'attendanceRecords', 'leaveRequests', 'taskAssignments', 'eventAssignments');

        return Inertia::render('Admin/Employees/Show', [
            'employee' => $employee,
        ]);
    }

    public function edit(Employee $employee): Response
    {
        $this->authorize('update', $employee);

        $users = User::where('organization_id', auth()->user()->organization_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        $departments = Department::where('organization_id', auth()->user()->organization_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Admin/Employees/Edit', [
            'employee' => $employee,
            'users' => $users,
            'departments' => $departments,
            'employmentTypes' => Employee::EMPLOYMENT_TYPES,
        ]);
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $this->authorize('update', $employee);

        $data = $request->validated();
        $employee->update($data);

        return redirect()->route('admin.hr.employees.show', $employee)
            ->with('success', 'Funcionário atualizado com sucesso!');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $this->authorize('delete', $employee);

        $employee->delete();

        return redirect()->route('admin.hr.employees.index')
            ->with('success', 'Funcionário eliminado com sucesso!');
    }

    public function updateStatus(UpdateEmployeeStatusRequest $request, Employee $employee): RedirectResponse
    {
        $this->authorize('update', $employee);

        $this->updateStatusAction->execute($employee, $request->validated());

        return redirect()->back()
            ->with('success', 'Status do funcionário atualizado com sucesso!');
    }
}
