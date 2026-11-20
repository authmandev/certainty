<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaymentReceiptController;
use App\Http\Controllers\StudyController;
use App\Http\Controllers\StudyReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::inertia('privacy', 'public/Privacy')->name('privacy');
Route::inertia('terms', 'public/Terms')->name('terms');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::resource('studies', StudyController::class)->only(['create', 'store', 'show', 'edit', 'update']);

    Route::post('studies/{study}/receipts', [PaymentReceiptController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('studies.receipts.store');
    Route::post('studies/{study}/finalize', [StudyReportController::class, 'store'])->name('studies.finalize');
    Route::get('studies/{study}/report', [StudyReportController::class, 'show'])->name('studies.report');

    Route::get('receipts/{receipt}', [PaymentReceiptController::class, 'show'])->name('receipts.show');
});

require __DIR__.'/settings.php';
require __DIR__.'/admin.php';
