<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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
        $user = auth()->user();
        return view('users.profile-setting', [
            'user' => $user
        ]);
    }

    function updateProfile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:4',
            'phone' => 'required|digits:11|unique:users,phone,' . $user->id,
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        if ($request->hasFile('image')) {
            if ($user->image && $user->image !== 'default-user.png') {
                Storage::disk('public')->delete('images/' . $user->image);
            }
            $path = $request->file('image')->store('images', 'public');
            $validated['image'] = basename($path);
        }

        $user->update($validated);

        return redirect()
            ->route('profile.settings.form')
            ->with('success', 'Profile updated successfully!');
    }


    function editUserForm($id)
    {
        $user = User::findOrFail($id);

        return view('users.edit-user', [
            'user' => $user
        ]);
    }

    function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:4',
            'phone' => 'required|digits:11|unique:users,phone,' . $user->id,
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        if ($request->hasFile('image')) {
            if ($user->image && $user->image !== 'default-user.png') {
                Storage::disk('public')->delete('images/' . $user->image);
            }
            $path = $request->file('image')->store('images', 'public');
            $validated['image'] = basename($path);
        }

        $user->update($validated);

        return redirect()
            ->route('allUsers')
            ->with('success', 'User updated successfully!');
    }

    function deleteUser($id)
    {
        $user = User::findOrFail($id);
        if ($user->image && $user->image !== 'default-user.png') {
            Storage::disk('public')->delete('images/' . $user->image);
        }

        $user->delete();

        return redirect()
            ->route('allUsers')
            ->with('success', 'User deleted successfully!');
    }
}
