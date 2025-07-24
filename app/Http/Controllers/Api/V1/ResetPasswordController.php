<?php

namespace App\Http\Controllers\Api\V1;

use App\GeneralTrait;
use App\Http\Controllers\Controller;
use App\Notifications\ResetPasswordNotification;
use Carbon\Carbon;
use DragonCode\Contracts\Cashier\Auth\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    use GeneralTrait;
    public function resetPassword()
    {
        $user = auth()->user();
        // return $user;
        if (!$user) {
            return $this->returnError(404,  'البريد الإلكتروني غير مسجل');
        }
        $token = rand(100000, 999999);
        $expiresAt = Carbon::now()->addMinutes(30);
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            ['token' => Hash::make($token), 'created_at' => now()]
        );
        $user->notify(new ResetPasswordNotification($token));
        return $this->returnData(['expires_at' => $expiresAt], 'تم إرسال رمز إعادة التعيين');
    }

    public function verifyCodeRest(Request $request)
    {
        // return "yes";
        $user = auth()->user();
        $record = DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->first();
        if (!Hash::check($request->code, $record->token)) {
            return $this->returnError(400, 'الرمز غير صحيح');
        }
        return $this->returnData(200, 'تم التحقق بنجاح');
    }

    public function updatedPassword(Request $request)
    {
        // return "yes";
        $user = auth()->user();
        if (!$user) {
            return $this->returnError(404, 'لم يتم العثور على حساب.');
        }
        $updated = $user->update([
            'password' => Hash::make($request['password']),
        ]);
        if (!$updated) {
            return $this->returnError(500, 'فشل في تحديث كلمة المرور.');
        }
        DB::table('password_reset_tokens')->where('email', $request['email'])->delete();
        return $this->returnData(200,  'تم تحديث كلمة المرور بنجاح');
    }
}
