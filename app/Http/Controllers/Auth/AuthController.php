<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    function login(Request $request)
    {

        return view('auth.login');
    }

    function signup(Request $request)
    {


        return view('auth.signup');
    }
}
