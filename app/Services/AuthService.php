<?php

namespace App\Services;

use App\GeneralTrait;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService extends Services
{
    public function login($credentials)
    {
        $credential = $credentials->only(['username', 'password']);
        $token = JWTAuth::attempt($credential);

        $exist = User::where('username', $credentials->username)->first();
        if ($exist && !$token)
            throw ValidationException::withMessages([
                'username' => ['الباسسورد غير مطابقة لهذا الحساب'],
            ]);

        if (!$token)
            throw ValidationException::withMessages([
                'username' => ['الحساب غير موجود'],
            ]);

        $user = auth()->user();
        $user->token = $token;
        $user->loadMissing(['roles', 'roles.permissions:name', 'company']);
        // if (!Auth::attempt($credentials)) {
        //     throw ValidationException::withMessages([
        //         'username' => ['الباسسورد غير مطابقة لهذا الحساب'],
        //     ]);
        // }
        $user = User::with(['roles', 'roles.permissions:name', 'company'])->where('id', Auth::user()->id)->first();
        $user->tokens()->delete();
        // $token = $user->createToken('auth_token', ['*'], now()->addDay())->plainTextToken;
        $token = Auth::attempt($credentials);
        // $this->logActivity('تم تسجيل الدخول من قبل ' . $user->username);

        return ['token' => $token, 'user' => $user];
    }

    public function logout($user)
    {
        $this->logActivity('تم تسجيل الخروج من قبل ' . $user->username);
        $user->tokens()->delete();
        return true;
    }

    public function updateProfile(User $user, array $data)
    {
        $user->update($data);
        return $user;
    }
}
