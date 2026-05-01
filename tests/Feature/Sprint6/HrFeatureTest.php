<?php

namespace Tests\Feature\Sprint6;

use App\Models\User;
use App\Models\Organization;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Team;
use App\Models\AbsenceType;
use App\Models\LeaveRequest;
use App\Models\AttendanceRecord;
use Database\Seeders\OrganizationSeeder;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HrFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected Organization $organization;
    protected User $admin;
    protected User $employee;
    protected User $rh;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            OrganizationSeeder::class,
            RoleAndPermissionSeeder::class,
        ]);

        $this->organization = Organization::first();
        
        $this->admin = User::factory()->create(['organization_id' => $this->organization->id]);
        $this->admin->assignRole('admin_junta');

        $this->rh = User::factory()->create(['organization_id' => $this->organization->id]);
        $this->rh->assignRole('rh');

        $this->employee = User::factory()->create(['organization_id' => $this->organization->id]);
        $this->employee->assignRole('administrativo');
    }

    public function test_admin_consegue_listar_departamentos(): void
    {
        Department::factory(3)->create(['organization_id' => $this->organization->id]);

        $this->actingAs($this->admin)
            ->get(route('admin.hr.departments.index'))
            ->assertStatus(200);
    }

    public function test_rh_consegue_criar_departamento(): void
    {
        $this->actingAs($this->rh)
            ->post(route('admin.hr.departments.store'), [
                'name' => 'Secretaria',
                'description' => 'Departamento de Secretaria',
            ])
            ->assertRedirect(route('admin.hr.departments.index'));

        $this->assertDatabaseHas('departments', [
            'organization_id' => $this->organization->id,
            'name' => 'Secretaria',
        ]);
    }

    public function test_admin_consegue_criar_employee(): void
    {
        $department = Department::factory()->create(['organization_id' => $this->organization->id]);

        $this->actingAs($this->admin)
            ->post(route('admin.hr.employees.store'), [
                'department_id' => $department->id,
                'employment_type' => 'permanent',
                'role_title' => 'Secretária',
                'start_date' => now()->format('Y-m-d'),
                'phone' => '912345678',
            ])
            ->assertRedirect(route('admin.hr.employees.index'));

        $this->assertDatabaseHas('employees', [
            'organization_id' => $this->organization->id,
            'employment_type' => 'permanent',
        ]);
    }

    public function test_employee_number_e_gerado_automaticamente(): void
    {
        $department = Department::factory()->create(['organization_id' => $this->organization->id]);

        $this->actingAs($this->admin)
            ->post(route('admin.hr.employees.store'), [
                'department_id' => $department->id,
                'employment_type' => 'permanent',
                'role_title' => 'Assistente',
                'start_date' => now()->format('Y-m-d'),
            ]);

        $employee = Employee::where('organization_id', $this->organization->id)->first();
        $this->assertNotNull($employee->employee_number);
        $this->assertStringStartsWith('EMP-' . now()->year, $employee->employee_number);
    }

    public function test_admin_consegue_criar_equipa(): void
    {
        $department = Department::factory()->create(['organization_id' => $this->organization->id]);

        $this->actingAs($this->admin)
            ->post(route('admin.hr.teams.store'), [
                'department_id' => $department->id,
                'name' => 'Equipa de Manutenção',
                'description' => 'Responsável por manutenção',
            ])
            ->assertRedirect(route('admin.hr.teams.index'));

        $this->assertDatabaseHas('teams', [
            'organization_id' => $this->organization->id,
            'name' => 'Equipa de Manutenção',
        ]);
    }

    public function test_admin_consegue_criar_absence_type(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.hr.absence-types.store'), [
                'name' => 'Férias',
                'description' => 'Férias anuais',
                'requires_approval' => false,
                'is_paid' => true,
            ])
            ->assertRedirect(route('admin.hr.absence-types.index'));

        $this->assertDatabaseHas('absence_types', [
            'organization_id' => $this->organization->id,
            'name' => 'Férias',
        ]);
    }

    public function test_admin_consegue_criar_registro_presenca(): void
    {
        $employee = Employee::factory()->create(['organization_id' => $this->organization->id]);

        $this->actingAs($this->admin)
            ->post(route('admin.hr.attendance.store'), [
                'employee_id' => $employee->id,
                'date' => now()->format('Y-m-d'),
                'status' => 'present',
                'check_in' => '09:00',
                'check_out' => '17:00',
            ])
            ->assertRedirect(route('admin.hr.attendance.index'));

        $this->assertDatabaseHas('attendance_records', [
            'organization_id' => $this->organization->id,
            'employee_id' => $employee->id,
            'status' => 'present',
        ]);
    }

    public function test_admin_consegue_validar_registro_presenca(): void
    {
        $record = AttendanceRecord::factory()->create([
            'organization_id' => $this->organization->id,
            'validated_by' => null,
        ]);

        $this->actingAs($this->admin)
            ->patch(route('admin.hr.attendance.validate', $record), [])
            ->assertRedirect(route('admin.hr.attendance.index'));

        $record->refresh();
        $this->assertNotNull($record->validated_by);
        $this->assertNotNull($record->validated_at);
    }

    public function test_admin_consegue_criar_pedido_ferias(): void
    {
        $employee = Employee::factory()->create(['organization_id' => $this->organization->id]);
        $absenceType = AbsenceType::factory()->create(['organization_id' => $this->organization->id]);

        $this->actingAs($this->admin)
            ->post(route('admin.hr.leave-requests.store'), [
                'employee_id' => $employee->id,
                'absence_type_id' => $absenceType->id,
                'leave_type' => 'vacation',
                'start_date' => now()->addDay()->format('Y-m-d'),
                'end_date' => now()->addDays(6)->format('Y-m-d'),
                'reason' => 'Férias de verão',
            ])
            ->assertRedirect(route('admin.hr.leave-requests.index'));

        $this->assertDatabaseHas('leave_requests', [
            'organization_id' => $this->organization->id,
            'employee_id' => $employee->id,
            'leave_type' => 'vacation',
        ]);
    }

    public function test_admin_consegue_aprovar_pedido_ferias(): void
    {
        $leaveRequest = LeaveRequest::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'requested',
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.hr.leave-requests.approve', $leaveRequest), [])
            ->assertRedirect(route('admin.hr.leave-requests.show', $leaveRequest));

        $leaveRequest->refresh();
        $this->assertEquals('approved', $leaveRequest->status);
        $this->assertNotNull($leaveRequest->approved_by);
    }

    public function test_admin_consegue_rejeitar_pedido_ferias(): void
    {
        $leaveRequest = LeaveRequest::factory()->create([
            'organization_id' => $this->organization->id,
            'status' => 'requested',
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.hr.leave-requests.reject', $leaveRequest), [
                'rejection_reason' => 'Conflito de calendário',
            ])
            ->assertRedirect(route('admin.hr.leave-requests.show', $leaveRequest));

        $leaveRequest->refresh();
        $this->assertEquals('rejected', $leaveRequest->status);
        $this->assertNotNull($leaveRequest->rejected_by);
    }

    public function test_utilizador_sem_permissao_nao_consegue_listar_departamentos(): void
    {
        $this->actingAs($this->employee)
            ->get(route('admin.hr.departments.index'))
            ->assertForbidden();
    }

    public function test_utilizador_sem_permissao_nao_consegue_criar_employee(): void
    {
        $department = Department::factory()->create(['organization_id' => $this->organization->id]);

        $this->actingAs($this->employee)
            ->post(route('admin.hr.employees.store'), [
                'department_id' => $department->id,
                'employment_type' => 'permanent',
                'role_title' => 'Test',
                'start_date' => now()->format('Y-m-d'),
            ])
            ->assertForbidden();
    }
}

