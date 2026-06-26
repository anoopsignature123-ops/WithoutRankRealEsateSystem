<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function getAll()
    {
        $authUser = Auth::user();
        if ($authUser->hasRole('super-admin')) {
            return User::with(['roles', 'creator'])->where('id', '!=', Auth::id())->latest()->get();
        }

        return User::with(['roles', 'creator'])
            ->where('created_by', Auth::id())
            ->where('id', '!=', Auth::id())
            ->latest()
            ->get();
    }

    public function create(array $data)
    {
        $data['profile_image'] = uploadFile($data['profile_image'] ?? null, 'users');
        
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'plain_text' => $data['password'],
            'status' => $data['status'] ?? 'active',
            'profile_image' => $data['profile_image'],
            'created_by' => Auth::id(),
        ]);
        if (! empty($data['role'])) {
            $user->assignRole($data['role']);
        }

        return $user;
    }

    public function find($id)
    {
        return User::with('roles')->findOrFail($id);
    }

    public function update($id, array $data)
    {
        $user = User::findOrFail($id);
        if (isset($data['profile_image'])) {
            deleteFile($user->profile_image);
            $data['profile_image'] = uploadFile($data['profile_image'], 'users');
        }
        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'status' => $data['status'] ?? $user->status,
            'updated_by' => Auth::id(),
            'profile_image' => $data['profile_image']
                ?? $user->profile_image,
        ]);
        if (! empty($data['role'])) {
            $user->syncRoles([$data['role']]);
        }

        return $user;
    }

    public function delete($id)
    {
        return User::findOrFail($id)->delete();
    }
}
