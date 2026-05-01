<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Actions\Hr\CreateDepartmentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Hr\StoreDepartmentRequest;
use App\Http\Requests\Hr\UpdateDepartmentRequest;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class DepartmentController extends Controller
{
    public function __construct(
        private readonly CreateDepartmentAction $createAction,
    ) {}
    public function index(): Response
    {
        $this->authorize('viewAny', Department::class);

        $departments = Department::where('organization_id', auth()->user()->organization_id)
            ->with('manager', 'employees', 'teams')
            ->orderBy('name')
            ->paginate(10);

        return Inertia::render('Admin/Departments/Index', [
            'departments' => $departments,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Department::class);

        $users = \App\Models\User::where('organization_id', auth()->user()->organization_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Admin/Departments/Create', [
            'users' => $users,
        ]);
    }

    public function store(StoreDepartmentRequest $request): RedirectResponse
    {
        $this->authorize('create', Department::class);

        $data = $request->validated();
        $data['organization_id'] = auth()->user()->organization_id;
        $data['slug'] = Str::slug($data['slug'] ?? $data['name']);

        $this->createAction->execute($data);

        return redirect()->route('admin.hr.departments.index')
            ->with('success', 'Departamento criado com sucesso!');
    }

    public function show(Department $department): Response
    {
        $this->authorize('view', $department);

        $department->load('manager', 'employees', 'teams');

        return Inertia::render('Admin/Departments/Show', [
            'department' => $department,
        ]);
    }

    public function edit(Department $department): Response
    {
        $this->authorize('update', $department);

        $users = \App\Models\User::where('organization_id', auth()->user()->organization_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Admin/Departments/Edit', [
            'department' => $department,
            'users' => $users,
        ]);
    }

    public function update(UpdateDepartmentRequest $request, Department $department): RedirectResponse
    {
        $this->authorize('update', $department);

        $data = $request->validated();
        $data['slug'] = Str::slug($data['slug'] ?? $data['name']);

        $department->update($data);

        return redirect()->route('admin.hr.departments.show', $department)
            ->with('success', 'Departamento atualizado com sucesso!');
    }

    public function destroy(Department $department): RedirectResponse
    {
        $this->authorize('delete', $department);

        $department->delete();

        return redirect()->route('admin.hr.departments.index')
            ->with('success', 'Departamento eliminado com sucesso!');
    }
}
