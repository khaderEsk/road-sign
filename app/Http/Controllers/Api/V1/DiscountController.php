<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\DiscountRequest;
use App\Services\DiscountService;

class DiscountController extends Controller
{
    public function __construct(private DiscountService $discountService)
    {
    }
    public function store(DiscountRequest $request)
    {
        return response()->json($this->discountService->create($request->validated()));
    }
}
