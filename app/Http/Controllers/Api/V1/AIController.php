<?php

namespace App\Http\Controllers\Api\V1;

use App\GeneralTrait;
use App\Http\Controllers\Controller;
use App\Models\RoadSign;
use App\ProductType;
use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class AIController extends Controller
{
    use GeneralTrait;
    public function __construct(private AIService $aiService) {}

    public function recommendByLocationAndBudget(Request $request)
    {
        return $this->aiService->getAll($request->all());
    }

    
}
