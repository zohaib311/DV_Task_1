<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    function login()
    {
        return view('auth.login');
    }

    function signup()
    {
        return view('auth.signup');
    }

    function signupSubmit(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:4|confirmed',
            'phone' => 'required|size:11',
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
            ->route('login')
            ->with('success', 'Account created successfully. Please login.');
    }

    function loginSubmit(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->intended(route('allStudents'))->with('success', 'Logged in successfully!');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Logged out successfully.');
    }
}
