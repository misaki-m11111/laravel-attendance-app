<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceRequestController;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminStaffController;
use App\Http\Controllers\AdminAttendanceRequestController;

Route::get('/admin/login', [AuthenticatedSessionController::class, 'create'])
    ->middleware('guest:admin')
    ->name('admin.login');

Route::post('/admin/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware(['guest:admin', 'fortify.admin'])
    ->name('admin.login.store');

Route::post('/admin/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware(['auth:admin', 'fortify.admin'])
    ->name('admin.logout');


Route::middleware('auth:admin')->group(function () {
    Route::get(
        '/admin/attendance/list',
        [AdminAttendanceController::class, 'index']
    )->name('admin.attendance.list');

    Route::get(
        '/admin/attendance/{id}',
        [AdminAttendanceController::class, 'show']
    )
        ->where('id', '[0-9]+')
        ->name('admin.attendance.show');

    Route::put(
        '/admin/attendance/{id}',
        [AdminAttendanceController::class, 'update']
    )
        ->where('id', '[0-9]+')
        ->name('admin.attendance.update');

    Route::get(
        '/admin/staff/list',
        [AdminStaffController::class, 'index']
    )->name('admin.staff.list');

    Route::get(
        '/admin/attendance/staff/{id}',
        [AdminStaffController::class, 'show']
    )
        ->where('id', '[0-9]+')
        ->name('admin.staff.show');

    Route::get(
        '/admin/attendance/staff/{id}/csv',
        [AdminStaffController::class, 'exportCsv']
    )
        ->where('id', '[0-9]+')
        ->name('admin.staff.csv');

    Route::get(
        '/stamp_correction_request/approve/{attendance_correct_request_id}',
        [AdminAttendanceRequestController::class, 'show']
    )
        ->where('attendance_correct_request_id', '[0-9]+')
        ->name('admin.attendance.request.show');

    Route::patch(
        '/stamp_correction_request/approve/{attendance_correct_request_id}',
        [AdminAttendanceRequestController::class, 'approve']
    )
        ->where('attendance_correct_request_id', '[0-9]+')
        ->name('admin.attendance.request.approve');
});


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get(
        '/attendance',
        [AttendanceController::class, 'index']
    )->name('attendance.index');

    Route::post(
        '/attendance',
        [AttendanceController::class, 'store']
    )->name('attendance.store');

    Route::get(
        '/attendance/list',
        [AttendanceController::class, 'monthlyList']
    )->name('attendance.list');

    Route::get(
        '/attendance/detail/{id}',
        [AttendanceController::class, 'detail']
    )
        ->where('id', '[0-9]+')
        ->name('attendance.detail');

    Route::post(
        '/attendance/detail/{id}',
        [AttendanceRequestController::class, 'store']
    )
        ->where('id', '[0-9]+')
        ->name('attendance.request.store');
});

Route::get(
    '/stamp_correction_request/list',
    [AttendanceRequestController::class, 'index']
)
    ->middleware('auth:admin,web')
    ->name('attendance.request.index');
