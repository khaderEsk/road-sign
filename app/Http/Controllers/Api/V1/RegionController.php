<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegionRequest;
use App\Services\RegionService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;

class RegionController extends Controller
{
    public function __construct(protected RegionService $regionService)
    {
        return [
            'role_or_permission:view-regions|create-regions|edit-regions|delete-regions',
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('delete-regions'), only: ['destroy']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('view-regions'), only: ['index', 'show']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('edit-regions'), only: ['update']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('create-regions'), only: ['store']),
        ];
    }

    public function index()
    {
        return response()->json($this->regionService->getAll());
    }

    public function store(RegionRequest $request)
    {
        return response()->json($this->regionService->create($request->validated()));
    }

    public function show($id)
    {
        return response()->json($this->regionService->getById($id));
    }

    public function update(RegionRequest $request, $id)
    {
        return response()->json($this->regionService->update($id, $request->validated()));
    }

    public function destroy($id)
    {
        return response()->json(['deleted' => $this->regionService->delete($id)]);
    }

    public function getActiveByCity(Request $request)
    {
        return response()->json($this->regionService->getActiveByCity($request->input('city_id')));
    }
}
