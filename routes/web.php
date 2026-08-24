<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route::view('/show', 'allUsers')->name('allUsers');

Route::view('/add', 'add-user')->name('addUsersForm');
Route::post('/add', [StudentController::class, 'addUser'])->name('addUser');
Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::get('/signup', [AuthController::class, 'signup'])->name('signup');
Route::get('/show', [StudentController::class, 'allUsers'])->name('allUsers');
