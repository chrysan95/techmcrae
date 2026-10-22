<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Controllers
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\EmpPortalController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\LeaveStatusController;
use App\Http\Controllers\LeaveRequestController;

// ==================================
// 1. PUBLIC & AUTHENTICATION ROUTES
// ==================================
Route::get('/', function () {
    if (Auth::check()) {
        return Auth::user()->role === 'admin' ? redirect('/dashboard') : redirect('/portal');
    }
    return view('auth.guest_entry');
})->name('login');

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


// ======================
// 2. EMPLOYEE WORKSPACE 
// ======================
Route::middleware(['auth'])->group(function () {

    Route::get('/portal', [EmpPortalController::class, 'index'])->name('employee.portal');

    Route::post('/portal/clock-in',  [EmpPortalController::class, 'clockIn']);
    Route::post('/portal/clock-out', [EmpPortalController::class, 'clockOut']);
    Route::post('/portal/leave-request', [EmpPortalController::class, 'requestLeave']);
    Route::post('/portal/leave/{id}/withdraw', [EmpPortalController::class, 'withdrawLeave']);
});


// ===================
// 3. ADMIN WORKSPACE 
// ===================
Route::middleware(['auth', 'role:admin'])->group(function () {

    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    Route::get('/leave/status', [LeaveStatusController::class, 'index'])->name('admin.leave_status');

    Route::get('/leave/requests', [LeaveRequestController::class, 'index'])->name('admin.leavereq');
    Route::post('/leave/requests/approve-all', [LeaveRequestController::class, 'approveAllPending']);
    Route::post('/leave/requests/{id}/approve', [LeaveRequestController::class, 'approve']);
    Route::post('/leave/requests/{id}/reject', [LeaveRequestController::class, 'reject']);

    Route::get('/employees', [EmployeeController::class, 'index'])->name('admin.employees');

    Route::get('/attendance/records', [AttendanceController::class, 'index'])->name('admin.attendance');
});