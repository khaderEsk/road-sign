<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\GeneralTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPasswordRequest;
use Illuminate\Http\Request;
use App\Services\ResetPasswordCustomerService;

class ResetPasswordCustomerController extends Controller
{
    public function __construct(protected ResetPasswordCustomerService $resetPasswordCustomerService) {}

    use GeneralTrait;

    public function resetPassword()
    {
        return $this->resetPasswordCustomerService->resetPassword();
    }

    public function verifyCodeRest(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6'
        ]);
        return $this->resetPasswordCustomerService->verifyCodeRest($request->validated());
    }

    public function updatedPassword(ResetPasswordRequest $request)
    {
        return $this->resetPasswordCustomerService->updatedPassword($request->validated());
    }
}
