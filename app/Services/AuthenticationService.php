<?php

namespace App\Services;

use App\CustomerType;
use App\GeneralTrait;
use App\Mail\CustomerVerificationEmail;
use App\Models\Customer;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthenticationService extends Services
{
    use GeneralTrait;

    public function register(array $request)
    {
        DB::beginTransaction();
        try {
            $otpCode = rand(100000, 999999);
            $request['password'] = Hash::make($request['password']);
            $request['otp_code'] = $otpCode;
            $request['otp_expires_at'] = now()->addMinutes(10);
            $customer = Customer::create($request);
            $customer->otp_code = $otpCode;
            $customer->otp_expires_at = now()->addMinutes(10);
            $customer->save();
            Mail::to($customer->email)->send(new CustomerVerificationEmail([
                'name' => $customer->name,
                'otp' => $otpCode
            ]));
            DB::commit();
            return $this->returnData(200, 'تمت انشاء بنجاح، يجب فتح الإيميل وتأكيد الحساب');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function verify(array $request)
    {
        DB::beginTransaction();
        try {
            $customer = Customer::where('email', $request['email'])
                ->where('otp_code', $request['otp_code'])
                ->where('otp_expires_at', '>', now())
                ->first();
            if (!$customer) {
                return $this->returnError(400, 'كود التحقق غير صحيح أو منتهي الصلاحية');
            }
            if (!Hash::check($request['password'], $customer->password)) {
                return $this->returnError(400, 'كلمة المرور غير صحيحة');
            }
            $customer->email_verified_at = now();
            $customer->otp_code = null;
            $customer->otp_expires_at = null;
            $customer->save();
            $credentials = [
                'company_name' => $customer->company_name,
                'password' => $request['password']
            ];
            $token = auth('customer')->attempt($credentials);
            if (!$token) {
                return $this->returnError(400, 'فشل تسجيل الدخول للعميل');
            }
            $customer->token = $token;
            $customer->assignRole('customer');
            $customer->loadMissing(['roles']);
            DB::commit();
            return $this->returnData(['customer' => $customer, 'token' => $token], 'تم تفعيل الحساب بنجاح!');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->returnError($e->getCode(), $e->getMessage());
        }
    }

    public function resendOtp(array $request)
    {
        DB::beginTransaction();
        try {
            $customer = Customer::where('email', $request['email'])->first();
            if (!$customer) {
                return $this->returnError(404, 'البريد الإلكتروني غير مسجل');
            }
            if ($customer->hasVerifiedEmail()) {
                return $this->returnError(400, 'الحساب مفعل بالفعل');
            }
            $customer->sendVerificationEmail();
            DB::commit();
            return $this->returnData(200, 'تم إعادة إرسال كود التحقق');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->returnError($e->getCode(), $e->getMessage());
        }
    }

    public function login($request)
    {
        try {
            $credentials = $request->only(['company_name', 'password']);
            $customer = Customer::where('company_name', $credentials['company_name'])->first();
            if (!$customer) {
                return $this->returnError(404, 'الحساب غير موجود');
            }
            if ($customer->otp_code) {
                return $this->returnError(400, 'يجب عليك تأكيد حسابك ');
            }
            if (!$token = auth('customer')->attempt($credentials)) {
                return $this->returnError(400, 'كلمة المرور غير صحيحة');
            }
            $user = auth('customer')->user();
            $user->loadRole();
            return $this->returnData(['customer' => $user, 'token' => $token], 'تم تسجيل الدخول بنجاح');
        } catch (\Throwable $e) {
            return $this->returnError(500, 'حدث خطأ: ' . $e->getMessage());
        }
    }

    public function logout($request)
    {
        $token = $request->bearerToken();
        if ($token) {
            try {
                JWTAuth::setToken($token)->invalidate();
                return $this->returnData("تم تسجيل الخروج بنجاح", 200);
            } catch (TokenInvalidException $e) {
                return $this->returnError($e->getCode(), 'الحساب تم تسجيل الخروج منه');
            }
        } else {
            return $this->returnError("400", 'some thing went wrongs');
        }
    }

    public function profile()
    {
        try {
            DB::beginTransaction();
            $user = auth('customer')->user();
            if (!$user) {
                return $this->returnError(404, 'الحساب غير موجود');
            }
            $user = Customer::with('customers', 'bookings', 'payments', 'payments.user', 'discounts')
                ->find($user->id);
            DB::commit();
            return $this->returnData($user, 'تمت العملية بنجاح');
        } catch (\Exception $ex) {
            DB::rollBack();
            return $this->returnError("500", $ex->getMessage());
        }
    }

    public function UpdateProfile(array $data)
    {
        DB::beginTransaction();
        try {
            $customer = auth('customer')->user();
            $customer->update($data);
            $customer->save();
            $customer->password = Hash::make($data['password']);
            $customerTracking = $customer->customers()->first();
            if ($data['is_tracking'] == 1) {
                if (isset($customerTracking)) {
                    $customerTracking->update($data['customer']);
                } else {
                    $customerTrackings = new Customer($data['customer']);
                    $customerTrackings->company_name = $customer->company_name;
                    $customerTrackings->type = CustomerType::TRACKING;
                    $customerTrackings->belong_id = $customer->id;
                    $customerTrackings->password = Hash::make(Str::random(10));
                    $customerTrackings->save();
                }
            } else {
                if (isset($customerTracking)) {
                    $customerTracking->delete();
                }
            }
            DB::commit();
            return $this->returnData(200, 'تم تحديث البيانات بنجاح');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->returnError($e->getTraceAsString(), $e->getMessage());
        }
    }


    public function refresh()
    {
        try {
            $token = auth('customer')->refresh();
            return $this->respondWithToken($token);
        } catch (TokenInvalidException $e) {
            return response()->json(['message' => 'توكن غير صالح'], 400);
        }
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('customer')->factory()->getTTL() * 60
        ], 200);
    }
}
