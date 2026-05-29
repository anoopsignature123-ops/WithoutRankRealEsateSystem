<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    
    public function index()
    {
        $user = Auth::user();

        return view('profile.profile', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);
        $user->name = $data['name'];
        if ($request->hasFile('image')) {
            $user->profile_image = uploadFile($request->file('image'),'profile',$user->profile_image);
        }

        $user->save();

        return back()->with('success', 'Profile updated successfully!');
    }

    public function changePasswordPage()
    {
        return view('profile.change_password');
    }
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|min:6|confirmed',
        ]);
        $user = Auth::user();
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'Current password is incorrect.',
            ]);
        }
        if (Hash::check($request->new_password, $user->password)) {
            return back()->withErrors([
                'new_password' => 'New password cannot be same as current password.',
            ]);
        }
        $user->password = Hash::make($request->new_password);
        $user->save();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')
            ->with('success', 'Password changed successfully. Please login again.');
    }
}