<?php

namespace App\Services;

use App\Models\Activity;
use Illuminate\Support\Facades\Auth;

class Services
{

    protected function logActivity(string $message)
    {
        Activity::create([
            'user_id' => auth()->user()->id,
            'activity' => $message,
        ]);
    }
}
