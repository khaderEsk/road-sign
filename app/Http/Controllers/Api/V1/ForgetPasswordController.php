<?php

namespace App\Http\Controllers\Api\V1;


use App\GeneralTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\ForgetPasswordRequest;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ForgetPasswordController extends Controller
{
    use GeneralTrait;

    public function sendResetCode(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return $this->returnError(404, 'الحساب غير مسجل');
        }
        $code = rand(100000, 999999);
        $expiresAt = Carbon::now()->addMinutes(30);
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['token' => Hash::make($code), 'created_at' => now()]
        );
        // return $user;
        $user->notify(new ResetPasswordNotification($code));
        return $this->returnData(['expires_at' => $expiresAt->toDateTimeString()],  'تم إرسال رمز إعادة التعيين',);
    }

    public function forgotPassword(ForgetPasswordRequest $request)
    {
        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();
        if (!$record || !Hash::check($request['token'], $record->token)) {
            return $this->returnError(400,  'الرمز غير صحيح أو منتهي الصلاحية');
        }
        $affectedRows = User::where('email', $request->email)
            ->update(['password' => Hash::make($request['password'])]);
        if ($affectedRows === 0) {
            return $this->returnError(404,  'لم يتم العثور على أي حسابات بهذا الإيميل');
        }
        DB::table('password_reset_tokens')->where('email', $request['email'])->delete();
        return $this->returnData(200,  'تم تحديث كلمة المرور بنجاح');
    }
}
