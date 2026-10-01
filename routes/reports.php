<?php

use App\Http\Controllers\Academic\AuditController;
use App\Http\Controllers\Academic\ReportController;
use App\Http\Controllers\StudentPortal\NotificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('academic')->name('academic.')->middleware(['auth', 'permission:reports.view'])->group(function () {
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
});
Route::prefix('academic')->name('academic.')->middleware(['auth', 'permission:audit.view'])->group(function () {
    Route::get('/audits', [AuditController::class, 'index'])->name('audits.index');
});
Route::prefix('student-portal/notifications')->name('student.notifications.')->middleware(['auth', 'permission:notifications.view-own'])->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::post('/{notification}/read', [NotificationController::class, 'read'])->name('read');
});
