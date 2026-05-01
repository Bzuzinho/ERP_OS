<?php

use App\Http\Controllers\Admin\Hr\DepartmentController;
use App\Http\Controllers\Admin\Hr\EmployeeController;
use App\Http\Controllers\Admin\Hr\TeamController;
use App\Http\Controllers\Admin\Hr\AttendanceRecordController;
use App\Http\Controllers\Admin\Hr\AbsenceTypeController;
use App\Http\Controllers\Admin\Hr\LeaveRequestController;
use Illuminate\Support\Facades\Route;

// Departments
Route::resource('hr/departments', DepartmentController::class)->names('hr.departments');

// Employees
Route::resource('hr/employees', EmployeeController::class)->names('hr.employees');
Route::patch('hr/employees/{employee}/status', [EmployeeController::class, 'updateStatus'])->name('hr.employees.status.update');

// Teams
Route::resource('hr/teams', TeamController::class)->names('hr.teams');
Route::post('hr/teams/{team}/members', [TeamController::class, 'addMember'])->name('hr.teams.members.store');
Route::delete('hr/teams/{team}/members', [TeamController::class, 'removeMember'])->name('hr.teams.members.destroy');

// Attendance
Route::resource('hr/attendance', AttendanceRecordController::class)->names('hr.attendance');
Route::patch('hr/attendance/{record}/validate', [AttendanceRecordController::class, 'validate'])->name('hr.attendance.validate');

// Absence Types
Route::resource('hr/absence-types', AbsenceTypeController::class)->names('hr.absence-types');

// Leave Requests
Route::resource('hr/leave-requests', LeaveRequestController::class)->names('hr.leave-requests');
Route::post('hr/leave-requests/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])->name('hr.leave-requests.approve');
Route::post('hr/leave-requests/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])->name('hr.leave-requests.reject');
Route::post('hr/leave-requests/{leaveRequest}/cancel', [LeaveRequestController::class, 'cancel'])->name('hr.leave-requests.cancel');

