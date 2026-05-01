<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Actions\Hr\CreateAttendanceRecordAction;
use App\Actions\Hr\ValidateAttendanceRecordAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Hr\StoreAttendanceRecordRequest;
use App\Http\Requests\Hr\UpdateAttendanceRecordRequest;
use App\Http\Requests\Hr\ValidateAttendanceRecordRequest;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceRecordController extends Controller
{
    public function __construct(
        private readonly CreateAttendanceRecordAction $createAction,
        private readonly ValidateAttendanceRecordAction $validateAction,
    ) {}

    public function index(): Response
    {
        $this->authorize('viewAny', AttendanceRecord::class);

        $records = AttendanceRecord::where('organization_id', auth()->user()->organization_id)
            ->with('employee.user', 'validator')
            ->when(request('employee'), fn($q) => $q->where('employee_id', request('employee')))
            ->when(request('date'), fn($q) => $q->where('date', request('date')))
            ->when(request('status'), fn($q) => $q->where('status', request('status')))
            ->when(request('validated'), function($q) {
                if (request('validated') === 'yes') {
                    $q->whereNotNull('validated_at');
                } elseif (request('validated') === 'no') {
                    $q->whereNull('validated_at');
                }
            })
            ->orderBy('date', 'desc')
            ->orderBy('employee_id')
            ->paginate(20);

        $employees = Employee::where('organization_id', auth()->user()->organization_id)
            ->orderBy('employee_number')
            ->get(['id', 'employee_number', 'role_title']);

        $statuses = AttendanceRecord::STATUSES;

        return Inertia::render('Admin/Attendance/Index', [
            'records' => $records,
            'employees' => $employees,
            'statuses' => $statuses,
            'filters' => request()->only(['employee', 'date', 'status', 'validated']),
        ]);
    }

    public function create(): Response
    {
        $employees = Employee::where('organization_id', auth()->user()->organization_id)
            ->where('is_active', true)
            ->orderBy('employee_number')
            ->get(['id', 'employee_number', 'role_title']);

        $statuses = AttendanceRecord::STATUSES;

        return Inertia::render('Admin/Attendance/Create', [
            'employees' => $employees,
            'statuses' => $statuses,
        ]);
    }

    public function store(StoreAttendanceRecordRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['organization_id'] = auth()->user()->organization_id;

        $this->createAction->execute($data);

        return redirect()->route('admin.hr.attendance.index')
            ->with('success', 'Presença registada com sucesso!');
    }

    public function show(AttendanceRecord $record): Response
    {
        $record->load('employee.user', 'validator', 'creator', 'comments', 'attachments');

        return Inertia::render('Admin/Attendance/Show', [
            'record' => $record,
        ]);
    }

    public function edit(AttendanceRecord $record): Response
    {
        $statuses = AttendanceRecord::STATUSES;

        return Inertia::render('Admin/Attendance/Edit', [
            'record' => $record,
            'statuses' => $statuses,
        ]);
    }

    public function update(UpdateAttendanceRecordRequest $request, AttendanceRecord $record): RedirectResponse
    {
        $data = $request->validated();

        // Recalculate worked minutes if check times changed
        if (isset($data['check_in']) && isset($data['check_out'])) {
            $service = new AttendanceService();
            $data['worked_minutes'] = $service->calculateWorkedMinutes(
                $data['check_in'],
                $data['check_out'],
                $data['break_minutes'] ?? 0
            );
        }

        $record->update($data);

        return redirect()->route('admin.hr.attendance.show', $record)
            ->with('success', 'Presença atualizada com sucesso!');
    }

    public function destroy(AttendanceRecord $record): RedirectResponse
    {
        $record->delete();

        return redirect()->route('admin.hr.attendance.index')
            ->with('success', 'Presença eliminada com sucesso!');
    }

    public function validate(ValidateAttendanceRecordRequest $request, AttendanceRecord $record): RedirectResponse
    {
        $this->authorize('validate', $record);

        $this->validateAction->execute($record);

        return redirect()->route('admin.hr.attendance.index')
            ->with('success', 'Presença validada com sucesso!');
    }
}
