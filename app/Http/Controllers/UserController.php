<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{


    function allUsers(Request $request)
    {

        $user = User::all();
        return view('users', ['users' => $user]);
    }

    function addUser(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|digits:11',
            'class' => 'required|string|min:2|max:10',
        ]);

        User::create($validated);

        return redirect()->back()->with('success', 'User added successfully!');
    }
}
