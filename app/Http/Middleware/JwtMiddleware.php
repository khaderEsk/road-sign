<?php

namespace App\Http\Middleware;

use App\GeneralTrait;
use App\Models\Customer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Exception;

class JwtMiddleware extends BaseMiddleware
{
    use GeneralTrait;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // التحقق من وجود التوكن في الرأس
            if (!$token = $request->bearerToken()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Authorization Token not found'
                ], 401);
            }

            // التحقق من صحة التوكن
            $payload = JWTAuth::setToken($token)->getPayload();

            // البحث عن العميل في قاعدة البيانات
            $customer = Customer::find($payload['customer_id']);

            if (!$customer) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Customer not found'
                ], 401);
            }

            // إضافة بيانات العميل إلى الطلب
            $request->merge(['customer' => $customer]);
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token expired'
            ], 401);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Token'
            ], 401);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Authorization Token error'
            ], 401);
        }

        return $next($request);
    }
}
