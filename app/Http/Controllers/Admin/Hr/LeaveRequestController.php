<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Actions\Hr\CreateLeaveRequestAction;
use App\Actions\Hr\ApproveLeaveRequestAction;
use App\Actions\Hr\RejectLeaveRequestAction;
use App\Actions\Hr\CancelLeaveRequestAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Hr\StoreLeaveRequestRequest;
use App\Http\Requests\Hr\ApproveLeaveRequestRequest;
use App\Http\Requests\Hr\RejectLeaveRequestRequest;
use App\Http\Requests\Hr\CancelLeaveRequestRequest;
use App\Models\LeaveRequest;
use App\Models\AbsenceType;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class LeaveRequestController extends Controller
{
    public function __construct(
        private readonly CreateLeaveRequestAction $createAction,
        private readonly ApproveLeaveRequestAction $approveAction,
        private readonly RejectLeaveRequestAction $rejectAction,
        private readonly CancelLeaveRequestAction $cancelAction,
    ) {}

    public function index(): Response
    {
        $this->authorize('viewAny', LeaveRequest::class);

        $requests = LeaveRequest::where('organization_id', auth()->user()->organization_id)
            ->with('employee.user', 'absenceType', 'approver', 'rejecter')
            ->when(request('status'), fn($q) => $q->where('status', request('status')))
            ->when(request('employee'), fn($q) => $q->where('employee_id', request('employee')))
            ->when(request('leave_type'), fn($q) => $q->where('leave_type', request('leave_type')))
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $employees = Employee::where('organization_id', auth()->user()->organization_id)
            ->orderBy('employee_number')
            ->get(['id', 'employee_number']);

        $statuses = LeaveRequest::STATUSES;
        $leaveTypes = LeaveRequest::LEAVE_TYPES;

        return Inertia::render('Admin/LeaveRequests/Index', [
            'requests' => $requests,
            'employees' => $employees,
            'statuses' => $statuses,
            'leaveTypes' => $leaveTypes,
            'filters' => request()->only(['status', 'employee', 'leave_type']),
        ]);
    }

    public function create(): Response
    {
        $employees = Employee::where('organization_id', auth()->user()->organization_id)
            ->where('is_active', true)
            ->orderBy('employee_number')
            ->get(['id', 'employee_number']);

        $absenceTypes = AbsenceType::where('organization_id', auth()->user()->organization_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Admin/LeaveRequests/Create', [
            'employees' => $employees,
            'absenceTypes' => $absenceTypes,
            'leaveTypes' => LeaveRequest::LEAVE_TYPES,
        ]);
    }

    public function store(StoreLeaveRequestRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['organization_id'] = auth()->user()->organization_id;

        $this->createAction->execute($data);

        return redirect()->route('admin.hr.leave-requests.index')
            ->with('success', 'Pedido de ausência criado com sucesso!');
    }

    public function show(LeaveRequest $request): Response
    {
        $request->load('employee.user', 'absenceType', 'approver', 'rejecter', 'canceller', 'comments', 'attachments');

        return Inertia::render('Admin/LeaveRequests/Show', [
            'request' => $request,
        ]);
    }

    public function edit(LeaveRequest $request): Response
    {
        $absenceTypes = AbsenceType::where('organization_id', auth()->user()->organization_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Admin/LeaveRequests/Edit', [
            'request' => $request,
            'absenceTypes' => $absenceTypes,
            'leaveTypes' => LeaveRequest::LEAVE_TYPES,
        ]);
    }

    public function approve(ApproveLeaveRequestRequest $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorize('approve', $leaveRequest);

        $this->approveAction->execute($leaveRequest, $request->validated());

        return redirect()->route('admin.hr.leave-requests.show', $leaveRequest)
            ->with('success', 'Pedido de ausência aprovado com sucesso!');
    }

    public function reject(RejectLeaveRequestRequest $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorize('reject', $leaveRequest);

        $this->rejectAction->execute($leaveRequest, $request->validated());

        return redirect()->route('admin.hr.leave-requests.show', $leaveRequest)
            ->with('success', 'Pedido de ausência rejeitado com sucesso!');
    }

    public function cancel(CancelLeaveRequestRequest $request, LeaveRequest $leaveRequest): RedirectResponse
    {
        $this->authorize('delete', $leaveRequest);

        $this->cancelAction->execute($leaveRequest, $request->validated());

        return redirect()->route('admin.hr.leave-requests.show', $leaveRequest)
            ->with('success', 'Pedido de ausência cancelado com sucesso!');
    }

    public function destroy(LeaveRequest $request): RedirectResponse
    {
        $this->authorize('delete', $request);

        $request->forceDelete();

        return redirect()->route('admin.hr.leave-requests.index')
            ->with('success', 'Pedido de ausência eliminado com sucesso!');
    }
}
