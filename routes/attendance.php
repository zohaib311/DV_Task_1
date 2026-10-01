<?php

use App\Http\Controllers\StudentPortal\AttendanceController as StudentAttendanceController;
use App\Http\Controllers\Teaching\AttendanceController;
use Illuminate\Support\Facades\Route;

Route::prefix('teaching/attendance')->name('teaching.attendance.')->middleware(['auth', 'permission:offerings.view-assigned', 'permission:attendance.manage-assigned'])->group(function () {
    Route::get('/', [AttendanceController::class, 'index'])->name('index');
    Route::get('/{offering}', [AttendanceController::class, 'offering'])->whereNumber('offering')->name('offering');
    Route::post('/{offering}', [AttendanceController::class, 'store'])->whereNumber('offering')->name('store');
    Route::get('/{offering}/sessions/{session}', [AttendanceController::class, 'show'])->whereNumber(['offering', 'session'])->name('show');
    Route::put('/{offering}/sessions/{session}', [AttendanceController::class, 'update'])->whereNumber(['offering', 'session'])->middleware('throttle:30,1')->name('update');
    Route::post('/{offering}/sessions/{session}/cancel', [AttendanceController::class, 'cancel'])->whereNumber(['offering', 'session'])->name('cancel');
});
Route::prefix('student-portal/attendance')->name('student.attendance.')->middleware(['auth', 'permission:student.attendance.view-own'])->group(function () {
    Route::get('/', [StudentAttendanceController::class, 'index'])->name('index');
    Route::get('/{course}', [StudentAttendanceController::class, 'show'])->whereNumber('course')->name('show');
});
