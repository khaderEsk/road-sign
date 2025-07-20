<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\BrokerRequest;
use App\Services\BrokerService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;

class BrokerController extends Controller
{
    public function __construct(protected BrokerService $brokerService) {
        return [
            'role_or_permission:view-brokers|create-brokers|edit-brokers|delete-brokers',
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('delete-brokers'), only: ['destroy']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('view-brokers'), only: ['index', 'show']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('edit-brokers'), only: ['update']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('create-brokers'), only: ['store']),
        ];
    }

    public function index()
    {
        return response()->json($this->brokerService->getAll());
    }

    public function store(BrokerRequest $request)
    {
        return response()->json($this->brokerService->create($request->validated()));
    }

    public function show($id)
    {
        return response()->json($this->brokerService->getById($id));
    }

    public function update(BrokerRequest $request, $id)
    {
        return response()->json($this->brokerService->update($id, $request->validated()));
    }

    public function destroy($id)
    {
        return response()->json(['deleted' => $this->brokerService->delete($id)]);
    }
}
