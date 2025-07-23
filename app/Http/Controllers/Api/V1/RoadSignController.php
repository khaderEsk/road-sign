<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoadSignRequest;
use App\Http\Requests\UpdateRoadsignsRequest;
use App\Services\RoadSignService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;

class RoadSignController extends Controller
{
    public function __construct(protected RoadSignService $roadSignService)
    {
        return [
            'role_or_permission:view-road-signs|create-road-signs|edit-road-signs|delete-road-signs',
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('delete-road-signs'), only: ['destroy']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('view-road-signs'), only: ['index', 'show']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('edit-road-signs'), only: ['update']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('create-road-signs'), only: ['store']),
        ];
    }

    public function index(Request $request)
    {
        return response()->json($this->roadSignService->getAll($request->all()));
    }

    public function store(RoadSignRequest $request)
    {
        return response()->json($this->roadSignService->create($request->validated()));
    }

    public function show($id)
    {
        return response()->json($this->roadSignService->getById($id));
    }

    public function update(UpdateRoadsignsRequest $request, $id)
    {
        return response()->json($this->roadSignService->update($id, $request->validated()));
    }

    public function destroy($id)
    {
        return response()->json(['deleted' => $this->roadSignService->delete($id)]);
    }


    public function getRoadsignsDontHaveBooking(Request $request)
    {
        return response()->json($this->roadSignService->getRoadsignsDontHaveBooking($request->all()));
    }

    public function getRoadsignsBookingByWeek(Request $request)
    {
        return response()->json($this->roadSignService->getRoadsignsBookingByWeek($request->all()));
    }

    public function getRoadSignsTemplate()
    {
        return response()->json($this->roadSignService->getRoadSignsTemplate());
    }

    public function getRoadSignsBookingsByCustomerWithTemplatesModel(Request $request)
    {
        return response()->json($this->roadSignService->getRoadSignsBookingsByCustomerWithTemplatesModel($request->all()));
    }
}
