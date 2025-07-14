<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserService extends Services
{
    public function getAll()
    {
        return User::with('roles','payments','bookings')->orderbyDesc('created_at')->get();
    }

    public function getById($id)
    {
        return User::with('roles','payments','bookings', 'activities','company')->findOrFail($id);
    }

    public function create(array $data)
    {
        $user = User::create($data);
        if (isset($data['roles'])) {
            $user->syncRoles($data['roles']);
        }
        if (isset($data['permissions'])) {
            $user->syncPermissions($data['permissions']);
        }
        $this->logActivity('تم إنشاء المستخدم: ' . $user->username . " بواسطة المستخدم: " . auth()->user()->username);
        return $user;
    }

    public function update($id, array $data)
    {
        $user = User::findOrFail($id);
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $user->update($data);
        if (isset($data['roles'])) {
            $user->syncRoles($data['roles']);
        }
        if (isset($data['permissions'])) {
            $user->syncPermissions($data['permissions']);
        }
        $this->logActivity('تم تحديث المستخدم: ' . $user->username . " بواسطة المستخدم: " . auth()->user()->username);
        return $user;
    }

    public function delete($id)
    {
        $user = User::findOrFail($id);
        $this->logActivity('تم حذف المستخدم: ' . $user->username . " بواسطة المستخدم: " . auth()->user()->username);
        return $user->delete();
    }

}
