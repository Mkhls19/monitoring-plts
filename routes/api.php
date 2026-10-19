<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MonitoringController;
use App\Http\Controllers\Api\HistoryController;
use App\Http\Controllers\Api\ForecastController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Api\SystemController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Rute ESP32/Inverter (Tidak dilindungi auth agar hardware bisa post)
Route::post('/data', [MonitoringController::class, 'receiveData']);

// Rute Frontend
Route::prefix('v1')->group(function () {
    Route::get('/latest', [MonitoringController::class, 'getLatestData']);
    Route::get('/history', [HistoryController::class, 'getHistory']);
    Route::get('/forecast', [ForecastController::class, 'getEnergyForecast']);
    Route::get('/report', [ReportController::class, 'getData']);
});
