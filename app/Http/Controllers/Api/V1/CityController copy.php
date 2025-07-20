<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CityRequest;
use App\Services\CityService;
use Illuminate\Routing\Controllers\Middleware;

class CityController extends Controller
{
    public function __construct(protected CityService $cityService)
    {
        return [
            'role_or_permission:view-cities|create-cities|edit-cities|delete-cities',
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('delete-cities'), only: ['destroy']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('view-cities'), only: ['index', 'show']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('edit-cities'), only: ['update']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('create-cities'), only: ['store']),
        ];
    }

    public function index()
    {
        return response()->json($this->cityService->getAll());
    }

    public function store(CityRequest $request)
    {
        return response()->json($this->cityService->create($request->validated()));
    }

    public function show($id)
    {
        return response()->json($this->cityService->getById($id));
    }

    public function update(CityRequest $request, $id)
    {
        return response()->json($this->cityService->update($id, $request->validated()));
    }

    public function destroy($id)
    {
        return response()->json(['deleted' => $this->cityService->delete($id)]);
    }

    public function getActive()
    {
        return response()->json($this->cityService->getActive());
    }
}
