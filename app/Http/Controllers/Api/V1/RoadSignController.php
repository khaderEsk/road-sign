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
    public function __construct(protected RoadSignService $roadSignService) {}

    public function getAllRoadSing()
    {
        return $this->roadSignService->getAllRoadSing();
    }
    public function RoadSingSites()
    {
        return $this->roadSignService->RoadSingSites();
    }
    public function getById($id)
    {
        return $this->roadSignService->getById($id);
    }

    public function getRoadSingsFilter(Request $request)
    {
        return $this->roadSignService->getRoadSingsFilter($request);
    }
}
