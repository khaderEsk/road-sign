<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ActivityService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;

class ActivityController extends Controller
{
    public function __construct(private ActivityService $activityService)
    {
        return [
            'permission:view-activities|create-activities|edit-activities|delete-activities',
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('delete-activities'), only: ['destroy']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('view-activities'), only: ['index', 'show']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('edit-activities'), only: ['update']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('create-activities'), only: ['store']),
        ];
    }
    public function getActivityiesUserById(Request $request)
    {
        return response()->json($this->activityService->getUserActivitiesById($request->input('user_id')));
    }
}
