<?php

use App\Http\Controllers\StudentPortal\PortalController;
use App\Http\Controllers\StudentPortal\CourseRegistrationController;
use App\Http\Controllers\StudentPortal\AssessmentSubmissionController;
use Illuminate\Support\Facades\Route;

Route::prefix('student-portal')->name('student.')->middleware('auth')->controller(PortalController::class)->group(function () {
    Route::get('/', 'dashboard')->middleware('permission:student.profile.view-own')->name('dashboard');
    Route::get('/courses', 'courses')->middleware('permission:student.courses.view-own')->name('courses');
    Route::get('/results', 'results')->middleware('permission:student.result.view-own')->name('results.index');
    Route::get('/results/{result}', 'result')->whereNumber('result')->middleware('permission:student.result.view-own')->name('results.show');
    Route::get('/assessments', 'assessments')->middleware('permission:student.assessments.view-own')->name('assessments.index');
    Route::get('/assessments/{course}/items/{assessment}/question-file', 'assessmentQuestion')->whereNumber(['course', 'assessment'])->middleware('permission:student.assessments.view-own')->name('assessments.question-file');
    Route::get('/assessments/{course}', 'assessment')->whereNumber('course')->middleware('permission:student.assessments.view-own')->name('assessments.show');
});

Route::prefix('student-portal/assessment-submissions')->name('student.assessment-submissions.')->middleware(['auth', 'permission:student.assessments.submit-own'])->controller(AssessmentSubmissionController::class)->group(function () {
    Route::post('/courses/{course}/assessments/{assessment}', 'store')->whereNumber(['course', 'assessment'])->middleware('throttle:10,1')->name('store');
    Route::get('/{submission}/download', 'studentDownload')->whereNumber('submission')->name('download');
});

Route::prefix('student-portal/course-registration')->name('student.registration.')->middleware(['auth', 'permission:student.registration.manage-own'])->controller(CourseRegistrationController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/', 'store')->middleware('throttle:10,1')->name('store');
});
