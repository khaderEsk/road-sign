<?php

namespace App\Http\Controllers\Api\V1;

use App\GeneralTrait;
use App\Http\Controllers\Controller;
use App\Models\RoadSign;
use App\ProductType;
use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AIController extends Controller
{
    use GeneralTrait;
    // public function __construct(private AIService $aiService) {}

    public function recommendByLocationAndBudget(Request $request)
    {
        try {
            $customer = auth('customer')->user();

            // جمع جميع معاملات الفلترة
            $filters = $request->only(['city_id', 'region_id', 'type', 'budget', 'page', 'perPage']);

            $query = RoadSign::with(['template.products', 'city', 'region'])
                ->when($request->city_id, fn($q) => $q->where('city_id', $request->city_id))
                ->when($request->region_id, fn($q) => $q->where('region_id', $request->region_id))
                ->orderBy('created_at', 'desc');

            if ($request->type) {
                $query->whereHas('template.products', function ($q) use ($request) {
                    $q->where('type', $request->type)
                        ->orWhere('type', ProductType::BOTH->value);
                });
            }

            $results = $query->get();

            if ($request->budget) {
                $results = $results->filter(function ($sign) use ($request) {
                    return $sign->template->products->sum('price') <= $request->budget;
                })->values();
            }

            $perPage = $request->perPage;
            $currentPage = $request->page;

            $paginatedResults = new LengthAwarePaginator(
                $results->forPage($currentPage, $perPage),
                $results->count(),
                $perPage,
                $currentPage,
                [
                    'path' => $request->url(),
                    'query' => $filters 
                ]
            );

            return response()->json([
                'status' => 200,
                'message' => $paginatedResults->isEmpty()
                    ? 'لم يتم العثور على اقتراحات مناسبة'
                    : 'تم العثور على اقتراحات مناسبة',
                'data' => $paginatedResults->items(),
                'meta' => [
                    'total_pages' => $paginatedResults->lastPage(),
                    'total' => $paginatedResults->total(),
                    'count' => count($paginatedResults->items()),
                    'current_page' => (int)$currentPage,
                    'per_page' => (int)$perPage
                ]
            ]);
        } catch (\Throwable $e) {
            return $this->returnError($e->getMessage(), 'حدث خطأ أثناء توليد الاقتراحات');
        }
    }
    // return $this->aiService->recommendByLocationAndBudget($request);

}
