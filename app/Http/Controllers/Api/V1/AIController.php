<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\AIService;
use Illuminate\Http\Request;

class AIController extends Controller
{
    public function __construct(private AIService $aiService) {}

    public function recommendByLocationAndBudget(Request $request)
    {
        return $this->aiService->recommendByLocationAndBudget($request);
    }
}
