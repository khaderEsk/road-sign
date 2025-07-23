<?php

namespace App\Http\Middleware;

use App\GeneralTrait;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CustomerRoleCheck
{
    use GeneralTrait;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth('customer')->check()) {
            $user = auth('customer')->user();
            if ($user->hasRole('customer')) {
                return $next($request);
            }
            return $this->returnError(403, 'لا تملك الصلاحية المناسبة');
        }
        return $this->returnError(401, 'يجب تسجيل الدخول أولاً');
    }
}
