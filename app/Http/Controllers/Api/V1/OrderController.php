<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Http\Requests\UpdateStatusOrderRequest;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;

class OrderController extends Controller
{
    public function __construct(protected OrderService $orderService)
    {
        return [
            'role_or_permission:view-orders|create-orders|edit-orders|delete-orders',
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('delete-orders'), only: ['destroy']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('view-orders'), only: ['index', 'show']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('edit-orders'), only: ['update']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('create-orders'), only: ['store']),
        ];
    }

    public function index(Request $request)
    {
        return response()->json($this->orderService->getAll($request->all()));
    }

    public function store(OrderRequest $request)
    {
        return response()->json($this->orderService->create($request->validated()));
    }

    public function show($id)
    {
        return response()->json($this->orderService->getById($id));
    }

    public function update(OrderRequest $request, $id)
    {
        return response()->json($this->orderService->update($id, $request->validated()));
    }

    public function updateStatus(UpdateStatusOrderRequest $request, $id)
    {
        return response()->json(
            $this->orderService->updateStatus(
                $id,
                $request->validated('status')
            )
        );
    }

    public function destroy($id)
    {
        return response()->json(['deleted' => $this->orderService->delete($id)]);
    }
}
