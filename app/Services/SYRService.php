<?php

namespace App\Services;

use App\GeneralTrait;
use App\Models\Company;
use App\Models\RoadSign;

class SYRService
{
    use GeneralTrait;

    public function index()
    {
        try {
            $company = Company::first();
            return $this->returnData($company, 'تمت العملية بنجاح');
        } catch (\Throwable $e) {
            return $this->returnError($e->getTraceAsString(), $e->getMessage());
        }
    }
}
