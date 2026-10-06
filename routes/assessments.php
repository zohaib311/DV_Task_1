<?php

use App\Http\Controllers\Academic\AssessmentReviewController;
use App\Http\Controllers\Academic\AssessmentSchemeController;
use App\Http\Controllers\Teaching\AssessmentController;
use App\Http\Controllers\StudentPortal\AssessmentSubmissionController;
use Illuminate\Support\Facades\Route;

Route::prefix('teaching/assessments')->name('teaching.assessments.')->middleware(['auth', 'permission:offerings.view-assigned', 'permission:assessments.manage-assigned|marks.manage-assigned|results.submit'])->group(function () {
    Route::get('/', [AssessmentController::class, 'index'])->name('index');
    Route::get('/{offering}', [AssessmentController::class, 'show'])->whereNumber('offering')->name('show');
    Route::post('/{offering}', [AssessmentController::class, 'store'])->whereNumber('offering')->middleware('permission:assessments.manage-assigned')->name('store');
    Route::post('/{offering}/submit', [AssessmentController::class, 'submit'])->whereNumber('offering')->middleware(['permission:results.submit', 'throttle:10,1'])->name('submit');
    Route::get('/{offering}/items/{assessment}', [AssessmentController::class, 'edit'])->whereNumber(['offering', 'assessment'])->name('edit');
    Route::put('/{offering}/items/{assessment}', [AssessmentController::class, 'update'])->whereNumber(['offering', 'assessment'])->middleware('permission:assessments.manage-assigned')->name('update');
    Route::put('/{offering}/items/{assessment}/marks', [AssessmentController::class, 'marks'])->whereNumber(['offering', 'assessment'])->middleware('permission:marks.manage-assigned')->name('marks');
    Route::post('/{offering}/items/{assessment}/release', [AssessmentController::class, 'release'])->whereNumber(['offering', 'assessment'])->middleware('permission:marks.manage-assigned')->name('release');
    Route::get('/{offering}/items/{assessment}/student-submissions/{submission}', [AssessmentSubmissionController::class, 'teacherShow'])->whereNumber(['offering', 'assessment', 'submission'])->middleware('permission:marks.manage-assigned')->name('student-submissions.show');
    Route::get('/{offering}/items/{assessment}/student-submissions/{submission}/download', [AssessmentSubmissionController::class, 'teacherDownload'])->whereNumber(['offering', 'assessment', 'submission'])->middleware('permission:marks.manage-assigned')->name('student-submissions.download');
});
Route::prefix('academic/assessment-schemes')->name('academic.assessment-schemes.')->middleware(['auth', 'permission:offerings.manage'])->group(function () {
    Route::get('/{offering}', [AssessmentSchemeController::class, 'edit'])->name('edit');
    Route::post('/{offering}', [AssessmentSchemeController::class, 'store'])->name('store');
});
Route::prefix('academic/assessment-reviews')->name('academic.assessment-reviews.')->middleware(['auth', 'permission:assessments.review'])->group(function () {
    Route::get('/', [AssessmentReviewController::class, 'index'])->name('index');
    Route::get('/{submission}', [AssessmentReviewController::class, 'show'])->name('show');
    Route::post('/{submission}', [AssessmentReviewController::class, 'update'])->middleware('throttle:10,1')->name('update');
});
