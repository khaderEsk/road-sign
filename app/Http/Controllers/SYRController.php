<?php

namespace App\Http\Controllers;

use App\GeneralTrait;
use App\Models\City;
use App\Models\Company;
use App\Services\SYRService;
use Illuminate\Http\Request;

class SYRController extends Controller
{
    use GeneralTrait;

    public function __construct(protected SYRService $syrService) {}

    public function index()
    {
        return $this->syrService->index();
    }

    public function indexe()
    {
        return "yes";
        // return $this->cityService->getAllCity();
    }
}
