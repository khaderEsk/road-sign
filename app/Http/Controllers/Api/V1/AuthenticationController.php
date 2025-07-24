<?php

namespace App\Http\Controllers\Api\V1;

use App\GeneralTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerLoginRequest;
use App\Http\Requests\CustomerRequest;
use App\Http\Requests\RegisterCustomerRequest;
use App\Http\Requests\ResendVerifyRequest;
use App\Http\Requests\VerifyRequest;
use App\Services\AuthenticationService;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthenticationController extends Controller
{
    use GeneralTrait;
    public function __construct(protected AuthenticationService $authenticationService) {}

    public function register(RegisterCustomerRequest $request)
    {
        return $this->authenticationService->register($request->validated());
    }
    public function login(CustomerLoginRequest $request)
    {
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
    public function verify(VerifyRequest $request)
    {
        return $this->authenticationService->verify($request->validated());
    }

    public function resendOtp(ResendVerifyRequest $request)
    {
        return $this->authenticationService->resendOtp($request->validated());
    }
    public function refresh()
    {
        // return "yes";
        try {
            $newToken = JWTAuth::parseToken()->refresh();
            return $this->returnData(['token' => $newToken], 'تمت العملية بنجاح');
        } catch (\Throwable $e) {
            return $this->returnError(500, 'حدث خطأ: ' . $e->getMessage());
        }
    }
}
