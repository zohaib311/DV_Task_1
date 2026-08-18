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

            'name' => 'required|string|max:10',
            'email' => 'required|email',
            'phone' => 'required|number|max:11|min:11',
            'class' => 'required|string|max:10|min:2',

        ]);

        $user = User::create($validated);

        // dd($request->input());
        // $user = User::create([
        //     'name' => $validated->name,
        //     'email' => $validated->email,
        //     'phone' => $validated->phone,
        //     'class' => $validated->class,
        // ]);

        if ($user) {
            session()->flash('success', 'Product and image saved successfully!');
            return redirect('add-user');
        }
    }
}
