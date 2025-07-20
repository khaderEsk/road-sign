<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\GeneralTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\ForgetPasswordRequest;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Notifications\ResetPasswordNotification;
use App\Services\ForgetPasswordCustomerService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ForgetPasswordCustomerController extends Controller
{
    use GeneralTrait;
    public function __construct(protected ForgetPasswordCustomerService $forgetPasswordCustomerService) {}

    public function sendResetCode(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        return $this->forgetPasswordCustomerService->sendResetCode($request->validated());
    }


    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|digits:6'
        ]);
        return $this->forgetPasswordCustomerService->verifyCode($request->validated());
    }


    public function forgotPassword(ForgetPasswordRequest $request)
    {
        return $this->forgetPasswordCustomerService->forgotPassword($request->validated());
    }
}
