<?php

use App\Http\Controllers\Result\ResultModerationController;
use Illuminate\Support\Facades\Route;

Route::prefix('result/moderation')->name('results.moderation.')->middleware(['auth', 'permission:results.view-all'])->controller(ResultModerationController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/{enrollment}', 'show')->whereNumber('enrollment')->name('show');
    Route::post('/{enrollment}/approve', 'approve')->middleware('permission:results.approve')->name('approve');
    Route::post('/{enrollment}/publish', 'publish')->middleware('permission:results.publish')->name('publish');
    Route::put('/{enrollment}/correction', 'correct')->middleware(['permission:results.edit', 'permission:results.edit-published', 'permission:results.publish'])->name('correct');
});
