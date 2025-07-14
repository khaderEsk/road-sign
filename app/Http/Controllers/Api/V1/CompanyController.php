<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyRequest;
use App\Services\CompanyService;
use Illuminate\Http\Request;

class CompanyController extends Controller
{

    public function __construct(private CompanyService $companyService)
    {
    }

    public function update($id, CompanyRequest $request)
    {
        return Response()->json($this->companyService->update($id, $request->validated()));
    }

    public function show($id)
    {
        return Response()->json($this->companyService->getById($id));
    }
}
