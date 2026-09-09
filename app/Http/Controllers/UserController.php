<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    //
    function allUsers()
    {
        //
        $users = User::all();
        return view('users.users', ['users' => $users]);
    }
}
