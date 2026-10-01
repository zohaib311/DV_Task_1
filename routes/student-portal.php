<?php

use App\Http\Controllers\StudentPortal\PortalController;
use Illuminate\Support\Facades\Route;

Route::prefix('student-portal')->name('student.')->middleware('auth')->controller(PortalController::class)->group(function () {
    Route::get('/', 'dashboard')->middleware('permission:student.profile.view-own')->name('dashboard');
    Route::get('/courses', 'courses')->middleware('permission:student.courses.view-own')->name('courses');
    Route::get('/results', 'results')->middleware('permission:student.result.view-own')->name('results.index');
    Route::get('/results/{result}', 'result')->whereNumber('result')->middleware('permission:student.result.view-own')->name('results.show');
    Route::get('/assessments', 'assessments')->middleware('permission:student.assessments.view-own')->name('assessments.index');
    Route::get('/assessments/{course}', 'assessment')->whereNumber('course')->middleware('permission:student.assessments.view-own')->name('assessments.show');
});
