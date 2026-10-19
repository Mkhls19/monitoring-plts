<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\ReportController;

Route::get('/', function () {
    return redirect('/dashboard');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/monitoring', [DashboardController::class, 'monitoring'])->name('monitoring');
    Route::get('/history', [DashboardController::class, 'history'])->name('history');
    Route::get('/logs', [DashboardController::class, 'logs'])->name('logs');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports');
    Route::get('/prediction', [DashboardController::class, 'prediction'])->name('prediction');
});
