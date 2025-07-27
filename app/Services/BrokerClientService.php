<?php

namespace App\Services;

use App\GeneralTrait;
use App\Models\Broker;
use App\Models\Customer;

class BrokerClientService extends Services
{
    use GeneralTrait;

    public function getMyClient()
    {
        try {
            $broker = auth('broker')->user();
            return $broker->id;
            $customers = Customer::where('', $broker->id)->get();
        } catch (\Throwable $e) {
            return $this->returnError($e->getCode(), $e->getMessage());
        }
    }
}
