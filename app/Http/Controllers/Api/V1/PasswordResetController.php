<?php

namespace App\Http\Controllers\Api\V1;

use App\GeneralTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPasswordRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use App\Models\Customer;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    use GeneralTrait;
    //return $this->returnError($e->getCode(), $e->getMessage());
    //return $this->returnData($customer, 'تم تفعيل الحساب بنجاح!');
    public function sendResetCode(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $customer = Customer::where('email', $request->email)->first();
        if (!$customer) {
            return $this->returnError(404, 'البريد الإلكتروني غير مسجل');
        }
        $code = rand(100000, 999999);
        $expiresAt = Carbon::now()->addMinutes(30);
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['token' => Hash::make($code), 'created_at' => now()]
        );
        $customer->notify(new ResetPasswordNotification($code));
        return $this->returnData(['expires_at' => $expiresAt->toDateTimeString()],  'تم إرسال رمز إعادة التعيين',);
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|digits:6'
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record || !Hash::check($request->code, $record->token)) {
            return $this->returnError(400, 'الرمز غير صحيح');
        }

        $tempToken = Str::random(60);
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->update(['token' => Hash::make($tempToken)]);

        return response()->json([
            'message' => 'تم التحقق بنجاح',
            'temp_token' => $tempToken
        ]);
    }




    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $customer = Customer::where('email', $request->email)->first();

        if (!$customer) {
            return $this->returnError(404,  'البريد الإلكتروني غير مسجل');
        }

        $token = Str::random(6);
        $expiresAt = Carbon::now()->addMinutes(30);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['token' => Hash::make($token), 'created_at' => now()]
        );

        $customer->notify(new ResetPasswordNotification($token));

        return response()->json([
            'message' => 'تم إرسال رمز إعادة التعيين',
            'expires_at' => $expiresAt
        ]);
    }
    public function verifyToken(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string'
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record) {
            return $this->returnError(404, 'رمز إعادة التعيين غير موجود أو منتهي الصلاحية');
        }

        if (!Hash::check($request->token, $record->token)) {
            return $this->returnError(400, 'رمز إعادة التعيين غير صحيح');
        }

        return response()->json(['message' => 'الرمز صحيح']);
    }

    public function resetPassword(ResetPasswordRequest $request)
    {
        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();
        if (!$record || !Hash::check($request->token, $record->token)) {
            return response()->json(['error' => 'الرمز غير صحيح أو منتهي الصلاحية'], 400);
        }

        $affectedRows = Customer::where('email', $request->email)
            ->update(['password' => Hash::make($request->password)]);

        if ($affectedRows === 0) {
            return response()->json(['error' => 'لم يتم العثور على أي حسابات بهذا الإيميل'], 404);
        }
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'تم تحديث كلمة المرور بنجاح']);
    }
}
