<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoadSignRequest;
use App\Http\Requests\UpdateRoadsignsRequest;
use App\Services\RoadSignCustomerService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;

class RoadSignCustomerController extends Controller
{
    public function __construct(protected RoadSignCustomerService $roadSignService) {}

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
