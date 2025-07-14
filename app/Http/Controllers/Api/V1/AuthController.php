<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\Request;
use App\Services\AuthService;

class AuthController extends Controller
{
    public function __construct(protected AuthService $authService) {}

    public function login(LoginRequest $request)
    {

        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        return response()->json($this->authService->login($request));
    }

    public function logout(Request $request)
    {

        $this->authService->logout($request->user());
        return response()->json(['message' => 'Logged out']);
    }

    public function profile(Request $request)
    {
        return response()->json($request->user());
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        // dd($request->all());
        return response()->json($this->authService->updateProfile($request->user(), $request->validated()));
    }

    
}
