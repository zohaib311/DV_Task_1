<?php

use App\Http\Controllers\Teaching\DashboardController;
use App\Http\Controllers\Teaching\OfferingController;
use App\Http\Controllers\Teaching\LeaveController;
use Illuminate\Support\Facades\Route;

Route::prefix('teaching')->name('teaching.')->middleware(['auth', 'permission:offerings.view-assigned'])->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/offerings', [OfferingController::class, 'index'])->name('offerings.index');
    Route::get('/offerings/{offering}', [OfferingController::class, 'show'])->whereNumber('offering')->name('offerings.show');
});

Route::prefix('teaching/leaves')->name('teaching.leaves.')->middleware(['auth', 'permission:offerings.view-assigned', 'permission:leaves.review-assigned'])->controller(LeaveController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/{leave}', 'show')->whereNumber('leave')->name('show');
    Route::put('/{leave}', 'review')->whereNumber('leave')->middleware('throttle:20,1')->name('review');
    Route::get('/{leave}/attachment', 'attachment')->whereNumber('leave')->name('attachment');
});
