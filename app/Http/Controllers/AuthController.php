<?php

namespace App\Http\Controllers;

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
