<?php

use App\Http\Controllers\Documents\DocumentPdfController;
use App\Http\Controllers\Fee\FeePaymentController;
use App\Http\Controllers\Fee\FeeStructureController;
use App\Http\Controllers\StudentPortal\FeeController;
use Illuminate\Support\Facades\Route;

Route::prefix('student-portal/fees')->name('student.fees.')->middleware(['auth', 'permission:student.fees.view-own'])->group(function () {
    Route::controller(FeeController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/{invoice}/payments', 'submit')->whereNumber('invoice')->middleware('permission:student.fees.submit-own')->name('payments.submit');
        Route::get('/{invoice}/payments/{payment}/proof', 'proof')->whereNumber(['invoice', 'payment'])->name('payments.proof');
    });
    Route::get('/{invoice}/challan-pdf', [DocumentPdfController::class, 'feeChallanPdf'])->whereNumber('invoice')->name('challan-pdf');
});

Route::prefix('fees')->name('fees.')->middleware('auth')->group(function () {
    Route::get('/invoices/{invoice}/challan-pdf', [DocumentPdfController::class, 'feeChallanPdf'])->whereNumber('invoice')->middleware('permission:fees.review|fees.manage')->name('invoices.challan-pdf');
    Route::middleware('permission:fees.manage')->controller(FeeStructureController::class)->group(function () {
        Route::get('/structures', 'index')->name('structures.index');
        Route::post('/structures', 'store')->name('structures.store');
        Route::put('/structures/{structure}', 'update')->name('structures.update');
    });
    Route::middleware('permission:fees.review')->controller(FeePaymentController::class)->group(function () {
        Route::get('/payments', 'index')->name('payments.index');
        Route::get('/payments/{payment}/proof', 'proof')->whereNumber('payment')->name('payments.proof');
        Route::post('/payments/{payment}/approve', 'approve')->whereNumber('payment')->name('payments.approve');
        Route::post('/payments/{payment}/reject', 'reject')->whereNumber('payment')->name('payments.reject');
    });
});
