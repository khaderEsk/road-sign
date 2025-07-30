<?php

namespace App\Http\Middleware;

use App\GeneralTrait;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomerStatusIsConfirmed
{
    use GeneralTrait;
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $customer = auth('customer')->user();

        if (!$customer) {
            return $this->returnError(503, 'خطأ في المصادقة');
        }

        return match ($customer->status) {
            1 => $this->returnError(400, 'طلبك قيد المعالجة'),
            0 => $this->returnError(400, 'يجب اكمال البيانات اولا'),
            default => $next($request),
        };
    }
}
