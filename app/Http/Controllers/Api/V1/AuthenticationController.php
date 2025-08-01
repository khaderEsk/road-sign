<?php

namespace App\Http\Controllers\Api\V1;

use App\GeneralTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\CompleteInformationRequest;
use App\Http\Requests\CustomerLoginRequest;
use App\Http\Requests\CustomerRequest;
use App\Http\Requests\RegisterCustomerRequest;
use App\Http\Requests\ResendVerifyRequest;
use App\Http\Requests\UpdatedProfileCustomerRequest;
use App\Http\Requests\VerifyRequest;
use App\ImageTrait;
use App\Services\AuthenticationService;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthenticationController extends Controller
{
    use GeneralTrait;
    use ImageTrait;
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

    public function updateProfile(UpdatedProfileCustomerRequest $request)
    {
        $data = $request->validated();
        return $this->authenticationService->UpdateProfile($data);
    }


    public function CompleteInformation(CompleteInformationRequest $request)
    {
        $data = $request->validated();
        $data['img'] = $this->uploadImage($request, 'img', 'crn');
        return $this->authenticationService->CompleteInformation($data);
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
        try {
            $newToken = JWTAuth::parseToken()->refresh();
            return $this->returnData(['token' => $newToken], 'تمت العملية بنجاح');
        } catch (\Throwable $e) {
            return $this->returnError(500, 'حدث خطأ: ' . $e->getMessage());
        }
    }

    public function getStatus()
    {
        return $this->authenticationService->getStatus();
    }
}
