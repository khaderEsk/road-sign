<?php

namespace App\Http\Controllers\Api\V1;

use App\GeneralTrait;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    use GeneralTrait;
    public function redirectToGoogle()
    {
        $redirectUrl = Socialite::driver('google')
            ->stateless()
            ->redirect()
            ->getTargetUrl();
        return response()->json(['url' => $redirectUrl]);
    }


    public function handleGoogleCallback(Request $request)
    {
        try {
            if (!$request->has('code')) {
                return response()->json([
                    'error' => 'Invalid request',
                    'message' => 'Authorization code not found'
                ], 400);
            }

            $googleUser = Socialite::driver('google')
                ->stateless()
                ->user();

            // باقي الكود كما هو...

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Google authentication failed',
                'message' => $e->getMessage(),
                'details' => $request->all() // لأغراض debugging
            ], 401);
        }
    }
}
