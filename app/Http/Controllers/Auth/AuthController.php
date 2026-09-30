<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;
use Database\Seeders\AccessControlSeeder;
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
            'phone' => 'required|size:11|unique:users,phone',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

        ]);

        if ($request->hasFile('image')) {

            $path = $request->file('image')->store('images', 'public');

            $fileName = basename($path);
        } else {

            $fileName = 'default-user.png';
        }


        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'],
            'image' => $validated['image'] = $fileName,
        ]);

        // On a fresh installation, the first account becomes the initial
        // administrator. All later accounts remain unassigned until an
        // authorized administrator assigns an appropriate role.
        app(AccessControlSeeder::class)->run();


        return redirect()
            ->route('login')
            ->with('success', $user->hasRole('Super Admin')
                ? 'Initial administrator account created successfully. Please sign in.'
                : 'Account created successfully. An administrator must assign your system role before academic access is available.');
    }

    function loginSubmit(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            if (! $request->user()->roles()->exists()) {
                return redirect()->route('profile.settings.form')->with(
                    'success',
                    'Your account is pending role assignment. Please contact a system administrator.'
                );
            }

            $landingRoute = $request->user()->can('offerings.view-assigned') && ! $request->user()->can('offerings.manage')
                ? 'teaching.dashboard'
                : 'dashboardView';

            return redirect()->intended(route($landingRoute))->with('success', 'Logged in successfully!');
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
