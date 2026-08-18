<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route::view('/show', 'allUsers')->name('allUsers');

Route::view('/add', 'add-user')->name('addUsersForm');
Route::post('/add', [UserController::class, 'addUser'])->name('addUser');
Route::get('/show', [UserController::class, 'allUsers'])->name('allUsers');
