<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceRequestController;

Route::get('/admin/login', [AuthenticatedSessionController::class, 'create'])
    ->middleware('guest:admin')
    ->name('admin.login');

Route::post('/admin/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware(['guest:admin', 'fortify.admin'])
    ->name('admin.login.store');

Route::post('/admin/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware(['auth:admin', 'fortify.admin'])
    ->name('admin.logout');

Route::middleware('auth:admin')->get('/admin/home', function () {
    return '管理者用の仮ページです';
})->name('admin.home');

Route::middleware(['auth', 'verified'])->get('/home', function () {
    return view('home');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])
        ->name('attendance.index');

    Route::post('/attendance', [AttendanceController::class, 'store'])
        ->name('attendance.store');

    Route::get('/attendance/list', [AttendanceController::class, 'monthlyList'])->name('attendance.list');

    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'detail'])->name('attendance.detail');

    Route::post('/attendance/detail/{id}', [AttendanceRequestController::class, 'store'])->name('attendance.request.store');

    Route::get('/stamp_correction_request/list',[AttendanceRequestController::class,'index'])->name('attendance.request.index');
});
