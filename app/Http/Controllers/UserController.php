<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    //
    function addUserForm()
    {
        return view('users.add-user');
    }
    function allUsers()
    {
        //
        $users = User::all();
        return view('users.users', ['users' => $users]);
    }

    function addUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:4',
            'phone' => 'required|digits:11|unique:users,phone',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

        ]);

        if ($request->hasFile('image')) {

            $path = $request->file('image')->store('images', 'public');

            $fileName = basename($path);
        } else {

            $fileName = 'default-user.png';
        }


        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'],
            'image' => $validated['image'] = $fileName,
        ]);



        return redirect()
            ->route('allUsers')
            ->with('success', 'User Added successfully.');
    }

    function userSettingForm()
    {
        return view('users.profile-setting');
    }
}
