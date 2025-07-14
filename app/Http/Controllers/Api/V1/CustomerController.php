<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerRequest;
use App\Services\CustomerService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;

class CustomerController extends Controller
{
    public function __construct(protected CustomerService $customerService)
    {
        return [
            'role_or_permission:view-customers|create-customers|edit-customers|delete-customers',
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('delete-customers'), only: ['destroy']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('view-customers'), only: ['index', 'show']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('edit-customers'), only: ['update']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('create-customers'), only: ['store']),
        ];
    }

    public function index()
    {
        return response()->json($this->customerService->getAll());
    }

    public function store(CustomerRequest $request)
    {
        return response()->json($this->customerService->create($request->validated()));
    }

    public function show($id)
    {
        return response()->json($this->customerService->getById($id));
    }

    public function update(CustomerRequest $request, $id)
    {
        return response()->json($this->customerService->update($id, $request->validated()));
    }

    public function destroy($id)
    {
        return response()->json(['deleted' => $this->customerService->delete($id)]);
    }
}
