<?php

use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('notifications')->name('account.notifications.')->middleware(['auth', 'permission:notifications.view-own'])->controller(NotificationController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('/feed', 'feed')->middleware('throttle:60,1')->name('feed');
    Route::post('/read-all', 'readAll')->name('read-all');
    Route::post('/{notification}/read', 'read')->name('read');
});
