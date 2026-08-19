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
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('image')) {

            $path = $request->file('image')->store('images', 'public');

            $fullFileName = explode('/', $path);

            $fileName = $fullFileName[1];
        } else {

            $fileName = 'default-user.png';
        }

        $validated['image'] = $fileName;

        User::create($validated);

        return redirect()->back()->with('success', 'User added successfully!');
    }
}
