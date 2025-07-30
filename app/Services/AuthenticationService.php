<?php

namespace App\Services;

use App\CustomerType;
use App\GeneralTrait;
use App\Mail\CustomerVerificationEmail;
use App\Models\Broker;
use App\Models\Customer;
use App\Models\Role;
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
                'email' => $customer->email,
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
            return $this->returnData(['user' => $customer, 'token' => $token], 'تم تفعيل الحساب بنجاح!');
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
            $credentials = $request->only(['email', 'password']);

            $broker = Broker::where('email', $credentials['email'])->first();
            $customer = Customer::where('email', $credentials['email'])->first();

            if (!$broker && !$customer) {
                return $this->returnError(404, 'الحساب غير موجود');
            }

            $userType = $broker ? 'broker' : 'customer';
            $user = $broker ?: $customer;

            if ($userType === 'customer' && $user->otp_code) {
                return $this->returnError(400, 'يجب عليك تأكيد حسابك');
            }

            if (!$token = auth($userType)->attempt($credentials)) {
                return $this->returnError(400, 'كلمة المرور غير صحيحة');
            }
            $user->role = $userType;
            $responseData = [
                'user' => $user,
                'token' => $token
            ];

            return $this->returnData($responseData, 'تم تسجيل الدخول بنجاح');
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
            return $this->returnError(401, 'some thing went wrongs');
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
            if ($customer->status = 0 || $customer->status = 1) {
                DB::rollBack();
                return $this->returnError(400, 'يجب استكمال المعلومات أولاً.');
            }
            $customer->update($data);
            $customer->save();
            $customerTracking = $customer->customers()->first();
            if ($data['is_tracking'] == 1) {
                if (isset($customerTracking)) {
                    $customerTracking->update($data['customer']);
                } else {
                    $customerTrackings = new Customer($data['customer']);
                    $customerTrackings->company_name = $customer->company_name;
                    $customerTrackings->type = CustomerType::TRACKING;
                    $customerTrackings->belong_id = $customer->id;
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

    public function CompleteInformation(array $data)
    {
        DB::beginTransaction();
        try {
            $customer = auth('customer')->user();
            $customer->update($data);
            $customer->status = 1;
            $customer->save();
            DB::commit();
            return $this->returnData(200, 'تحت تقديم طلب استكمال معلومات');
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


    public function getStatus()
    {
        try {
            $customer = auth('customer')->user();
            if (!$customer) {
                return $this->returnError(401, 'العميل غير مسجل الدخول');
            }
            return match ($customer->status) {
                1 => $this->returnError(400, 'طلبك قيد المعالجة'),
                0 => $this->returnError(400, 'يجب اكمال الابيانات اولا'),
                default => $this->returnError(['status' => $customer->status], 'حسابك مؤكد'),
            };
        } catch (\Throwable $e) {
            return $this->returnError($e->getCode(), $e->getMessage());
        }
    }
}
