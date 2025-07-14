<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Routing\Controllers\Middleware;

class UserController extends Controller
{
    public function __construct(protected UserService $userService)
    {
        return [
            'role_or_permission:view-users|create-users|edit-users|delete-users',
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('delete-users'), only: ['destroy']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('view-users'), only: ['index', 'show']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('edit-users'), only: ['update']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('create-users'), only: ['store']),
        ];
    }

    public function index()
    {
        return response()->json($this->userService->getAll());
    }

    public function store(StoreUserRequest $request)
    {
        return response()->json($this->userService->create($request->validated()));
    }

    public function show($id)
    {
        return response()->json($this->userService->getById($id));
    }

    public function update(UpdateUserRequest $request, $id)
    {
        return response()->json($this->userService->update($id, $request->validated()));
    }

    public function destroy($id)
    {
        return response()->json(['deleted' => $this->userService->delete($id)]);
    }
}
