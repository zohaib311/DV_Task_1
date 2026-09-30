<?php

use App\Http\Controllers\Teaching\DashboardController;
use App\Http\Controllers\Teaching\OfferingController;
use Illuminate\Support\Facades\Route;

Route::prefix('teaching')->name('teaching.')->middleware(['auth', 'permission:offerings.view-assigned'])->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/offerings', [OfferingController::class, 'index'])->name('offerings.index');
    Route::get('/offerings/{offering}', [OfferingController::class, 'show'])->whereNumber('offering')->name('offerings.show');
});
