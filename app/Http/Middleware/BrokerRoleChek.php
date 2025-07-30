<?php

namespace App\Http\Middleware;

use App\GeneralTrait;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BrokerRoleChek
{
    use GeneralTrait;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth('broker')->check()) {
            $user = auth('broker')->user();
            if (!$user) {
                return $this->returnError(403, 'لا تملك dsdad المناسبة');
            }
            if ($user->hasRole('broker')) {
                return $next($request);
            }
            return $this->returnError(403, 'لا تملك الصلاحية المناسبة');
        }
        return $next($request);
    }
}
