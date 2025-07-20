<?php

namespace App\Services;

use App\GeneralTrait;
use App\Notifications\ResetPasswordNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ResetPasswordCustomerService
{

    use GeneralTrait;

    public function resetPassword()
    {
        $customer = auth('customer')->user();
        if (!$customer) {
            return $this->returnError(404,  'البريد الإلكتروني غير مسجل');
        }
        $token = rand(100000, 999999);
        $expiresAt = Carbon::now()->addMinutes(30);
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $customer->email],
            ['token' => Hash::make($token), 'created_at' => now()]
        );
        $customer->notify(new ResetPasswordNotification($token));
        return $this->returnData(['expires_at' => $expiresAt], 'تم إرسال رمز إعادة التعيين');
    }

    public function verifyCodeRest(array $request)
    {
        $customer = auth('customer')->user();
        $record = DB::table('password_reset_tokens')
            ->where('email', $customer->email)
            ->first();
        if (!Hash::check($request['code'], $record->token)) {
            return $this->returnError(400, 'الرمز غير صحيح');
        }
        return $this->returnData(200, 'تم التحقق بنجاح');
    }

    public function updatedPassword(array $request)
    {
        $customer = auth('customer')->user();
        if (!$customer) {
            return $this->returnError(404, 'لم يتم العثور على حساب.');
        }
        $updated = $customer->update([
            'password' => Hash::make($request['password']),
        ]);
        if (!$updated) {
            return $this->returnError(500, 'فشل في تحديث كلمة المرور.');
        }
        DB::table('password_reset_tokens')->where('email', $request['email'])->delete();
        return $this->returnData(200,  'تم تحديث كلمة المرور بنجاح');
    }
}
