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
    public function googleLogin()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function googleLoginCallback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();
        return "user";
        dd($googleUser);
    }
}
