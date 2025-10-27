<?php

use App\Http\Controllers\ScreeningController;
use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\SymptomController;
use App\Http\Controllers\ThresholdController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

/**
 * SCREENING ROUTES - PUBLIC
 */
Route::prefix('screening')->name('screening.')->group(function () {
    // Tampilkan form screening (daftar gejala)
    Route::get('/', [ScreeningController::class, 'index'])->name('index');

    // Submit jawaban screening
    Route::post('/', [ScreeningController::class, 'store'])->name('store');

    // Lihat hasil screening
    Route::get('/{sessionId}/result', [ScreeningController::class, 'result'])->name('result');

    // Lihat riwayat screening
    Route::get('/history', [ScreeningController::class, 'history'])->name('history');
});

/**
 * ANALYSIS ROUTES - ADMIN/PROFESSIONAL
 */
Route::prefix('analysis')->name('analysis.')->middleware(['auth'])->group(function () {
    // Dashboard analisis
    Route::get('/dashboard', [AnalysisController::class, 'dashboard'])->name('dashboard');

    // Detail analisis session tertentu
    Route::get('/session/{sessionId}', [AnalysisController::class, 'sessionDetail'])->name('session-detail');

    // Export data screening
    Route::get('/export', [AnalysisController::class, 'export'])->name('export');
});

/**
 * SYMPTOM MANAGEMENT ROUTES - ADMIN
 */
Route::prefix('symptoms')->name('symptoms.')->middleware(['auth', 'can:manage-symptoms'])->group(function () {
    Route::get('/', [SymptomController::class, 'index'])->name('index');
    Route::get('/create', [SymptomController::class, 'create'])->name('create');
    Route::post('/', [SymptomController::class, 'store'])->name('store');
    Route::get('/{symptom}/edit', [SymptomController::class, 'edit'])->name('edit');
    Route::put('/{symptom}', [SymptomController::class, 'update'])->name('update');
    Route::delete('/{symptom}', [SymptomController::class, 'destroy'])->name('destroy');
});

/**
 * API ROUTES
 */
Route::prefix('api')->name('api.')->group(function () {
    Route::get('/symptoms', [SymptomController::class, 'getSymptoms'])->name('symptoms.list');
    Route::get('/thresholds', [ThresholdController::class, 'getThresholds'])->name('thresholds.list');
});

/**
 * THRESHOLD MANAGEMENT ROUTES - ADMIN
 */
Route::prefix('thresholds')->name('thresholds.')->middleware(['auth', 'can:manage-thresholds'])->group(function () {
    Route::get('/', [ThresholdController::class, 'index'])->name('index');
    Route::get('/create', [ThresholdController::class, 'create'])->name('create');
    Route::post('/', [ThresholdController::class, 'store'])->name('store');
    Route::get('/{threshold}/edit', [ThresholdController::class, 'edit'])->name('edit');
    Route::put('/{threshold}', [ThresholdController::class, 'update'])->name('update');
    Route::delete('/{threshold}', [ThresholdController::class, 'destroy'])->name('destroy');
});
