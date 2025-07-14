<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerLoginRequest;
use App\Http\Requests\CustomerRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Services\AuthenticationService;
use Illuminate\Http\Request;

class AuthenticationController extends Controller
{
    public function __construct(protected AuthenticationService $authenticationService) {}

    public function register(CustomerRequest $request)
    {
        // return $request;
        return $this->authenticationService->register($request->validated());
    }
    public function login(CustomerLoginRequest $request)
    {
        // return "yes";
        return $this->authenticationService->login($request);
    }

    public function logout(Request $request)
    {
        return $this->authenticationService->logout($request);
    }

    public function profile()
    {
        return $this->authenticationService->profile();
    }

    public function updateProfile(CustomerRequest $request)
    {
        return $this->authenticationService->UpdateProfile($request->validated());
    }
}
