<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContractRequest;
use App\Models\Contract;
use App\Services\ContractService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;

class ContractController extends Controller
{
    public function __construct(protected ContractService $service)
    {
        return [
            'role_or_permission:view-contracts|create-contracts|edit-contracts|delete-contracts',
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('delete-contracts'), only: ['destroy']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('view-contracts'), only: ['index', 'show']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('edit-contracts'), only: ['update']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('create-contracts'), only: ['store']),
        ];
    }

    public function index()
    {
        return response()->json($this->service->list());
    }

    public function store(ContractRequest $request)
    {

        return response()->json($this->service->create($request->validated()));
    }

    public function update(ContractRequest $request, Contract $contract)
    {

        return response()->json($this->service->update($contract, $request->validated()));
    }
    public function show($id)
    {
        return response()->json($this->service->getById($id));
    }

    public function destroy(Contract $contract)
    {
        return response()->json(['deleted' => $this->service->delete($contract)]);
    }
}
