<?php

use App\Http\Controllers\Teaching\DashboardController;
use App\Http\Controllers\Teaching\OfferingController;
use App\Http\Controllers\Teaching\LeaveController;
use Illuminate\Support\Facades\Route;

Route::prefix('teaching')->name('teaching.')->middleware(['auth', 'permission:offerings.view-assigned'])->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/offerings', [OfferingController::class, 'index'])->name('offerings.index');
    Route::get('/offerings/{offering}', [OfferingController::class, 'show'])->whereNumber('offering')->name('offerings.show');
    Route::get('/timetable', [\App\Http\Controllers\Teaching\TimetableController::class, 'index'])->name('timetable.index');

    // LMS Lite & Class Materials
    Route::prefix('offerings/{offering}/lms')->name('offerings.lms.')->whereNumber('offering')->middleware('permission:lms.manage-assigned')->controller(\App\Http\Controllers\Teaching\LmsController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/materials', 'storeMaterial')->name('materials.store');
        Route::get('/materials/{material}/download', 'downloadMaterial')->whereNumber('material')->name('materials.download');
        Route::delete('/materials/{material}', 'destroyMaterial')->whereNumber('material')->name('materials.destroy');
        Route::post('/announcements', 'storeAnnouncement')->name('announcements.store');
        Route::delete('/announcements/{announcement}', 'destroyAnnouncement')->whereNumber('announcement')->name('announcements.destroy');
        Route::get('/quizzes/create', 'createQuiz')->name('quizzes.create');
        Route::post('/quizzes', 'storeQuiz')->name('quizzes.store');
        Route::get('/quizzes/{quiz}', 'showQuiz')->whereNumber('quiz')->name('quizzes.show');
        Route::delete('/quizzes/{quiz}', 'destroyQuiz')->whereNumber('quiz')->name('quizzes.destroy');
    });
});

Route::prefix('teaching/leaves')->name('teaching.leaves.')->middleware(['auth', 'permission:offerings.view-assigned', 'permission:leaves.review-assigned'])->controller(LeaveController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/{leave}', 'show')->whereNumber('leave')->name('show');
    Route::put('/{leave}', 'review')->whereNumber('leave')->middleware('throttle:20,1')->name('review');
    Route::get('/{leave}/attachment', 'attachment')->whereNumber('leave')->name('attachment');
});
