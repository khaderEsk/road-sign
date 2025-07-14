<?php

namespace App\Services;

use App\Models\Activity;

class ActivityService
{
    public function getUserActivitiesById(int $user_id)
    {
        return Activity::query()
            ->where('user_id', $user_id)
            ->orderbyDesc('created_at')->get();
    }
}
