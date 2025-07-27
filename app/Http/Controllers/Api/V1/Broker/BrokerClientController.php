<?php

namespace App\Http\Controllers\Api\V1\Broker;

use App\Http\Controllers\Controller;
use App\Services\BrokerClientService;
use Illuminate\Http\Request;

class BrokerClientController extends Controller
{
    public function __construct(protected BrokerClientService $brokerClientService) {}
    public function index() {
        return $this->brokerClientService->getMyClient();
    }
}
