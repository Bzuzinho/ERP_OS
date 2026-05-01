<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Actions\Hr\CreateTeamAction;
use App\Actions\Hr\AddEmployeeToTeamAction;
use App\Actions\Hr\RemoveEmployeeFromTeamAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Hr\StoreTeamRequest;
use App\Http\Requests\Hr\UpdateTeamRequest;
use App\Http\Requests\Hr\AddTeamMemberRequest;
use App\Http\Requests\Hr\RemoveTeamMemberRequest;
use App\Models\Team;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TeamController extends Controller
{
    public function __construct(
        private readonly CreateTeamAction $createAction,
        private readonly AddEmployeeToTeamAction $addMemberAction,
        private readonly RemoveEmployeeFromTeamAction $removeMemberAction,
    ) {}

    public function index(): Response
    {
        $this->authorize('viewAny', Team::class);

        $teams = Team::where('organization_id', auth()->user()->organization_id)
            ->with('department', 'leader', 'teamMembers.employee')
            ->when(request('department'), fn($q) => $q->where('department_id', request('department')))
            ->when(request('status'), fn($q) => $q->where('is_active', request('status') === 'active'))
            ->when(request('search'), fn($q) => $q->where('name', 'like', '%' . request('search') . '%'))
            ->orderBy('name')
            ->paginate(15);

        $departments = Department::where('organization_id', auth()->user()->organization_id)
            ->pluck('name', 'id');

        return Inertia::render('Admin/Teams/Index', [
            'teams' => $teams,
            'departments' => $departments,
            'filters' => request()->only(['department', 'status', 'search']),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Team::class);

        $departments = Department::where('organization_id', auth()->user()->organization_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        $users = User::where('organization_id', auth()->user()->organization_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Admin/Teams/Create', [
            'departments' => $departments,
            'users' => $users,
        ]);
    }

    public function store(StoreTeamRequest $request): RedirectResponse
    {
        $this->authorize('create', Team::class);

        $data = $request->validated();
        $data['organization_id'] = auth()->user()->organization_id;
        $data['slug'] = \Illuminate\Support\Str::slug($data['slug'] ?? $data['name']);

        $this->createAction->execute($data);

        return redirect()->route('admin.hr.teams.index')
            ->with('success', 'Equipa criada com sucesso!');
    }

    public function show(Team $team): Response
    {
        $this->authorize('view', $team);

        $team->load('department', 'leader', 'teamMembers.employee');

        $activeMembers = $team->teamMembers()->where('is_active', true)->with('employee')->get();
        $inactiveMembers = $team->teamMembers()->where('is_active', false)->with('employee')->get();

        $availableEmployees = Employee::where('organization_id', auth()->user()->organization_id)
            ->whereNotIn('id', $activeMembers->pluck('employee_id'))
            ->orderBy('employee_number')
            ->get(['id', 'employee_number', 'role_title']);

        return Inertia::render('Admin/Teams/Show', [
            'team' => $team,
            'activeMembers' => $activeMembers,
            'inactiveMembers' => $inactiveMembers,
            'availableEmployees' => $availableEmployees,
        ]);
    }

    public function edit(Team $team): Response
    {
        $this->authorize('update', $team);

        $departments = Department::where('organization_id', auth()->user()->organization_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        $users = User::where('organization_id', auth()->user()->organization_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Admin/Teams/Edit', [
            'team' => $team,
            'departments' => $departments,
            'users' => $users,
        ]);
    }

    public function update(UpdateTeamRequest $request, Team $team): RedirectResponse
    {
        $this->authorize('update', $team);

        $data = $request->validated();
        $data['slug'] = \Illuminate\Support\Str::slug($data['slug'] ?? $data['name']);

        $team->update($data);

        return redirect()->route('admin.hr.teams.show', $team)
            ->with('success', 'Equipa atualizada com sucesso!');
    }

    public function destroy(Team $team): RedirectResponse
    {
        $this->authorize('delete', $team);

        $team->delete();

        return redirect()->route('admin.hr.teams.index')
            ->with('success', 'Equipa eliminada com sucesso!');
    }

    public function addMember(AddTeamMemberRequest $request, Team $team): RedirectResponse
    {
        $this->authorize('update', $team);

        $employee = Employee::findOrFail($request->employee_id);
        $this->addMemberAction->execute($team, $employee, $request->validated());

        return redirect()->back()
            ->with('success', 'Funcionário adicionado à equipa com sucesso!');
    }

    public function removeMember(RemoveTeamMemberRequest $request, Team $team): RedirectResponse
    {
        $this->authorize('update', $team);

        $employee = Employee::findOrFail($request->employee_id);
        $this->removeMemberAction->execute($team, $employee);

        return redirect()->back()
            ->with('success', 'Funcionário removido da equipa com sucesso!');
    }
}
