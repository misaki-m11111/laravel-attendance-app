<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

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
