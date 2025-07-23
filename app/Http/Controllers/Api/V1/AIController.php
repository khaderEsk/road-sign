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
            $customerId = auth('customer')->id();
            $query = RoadSign::with(['template.products', 'city', 'region'])
                ->when($request->city_id, fn($q) => $q->where('city_id', $request->city_id))
                ->when($request->region_id, fn($q) => $q->where('region_id', $request->region_id))
                ->when($customerId, function ($q) use ($customerId) {
                    $q->withCount(['favorite as is_favorite' => function ($query) use ($customerId) {
                        $query->where('customer_id', $customerId);
                    }]);
                }, function ($q) {
                    $q->selectRaw('*, 0 as is_favorite'); // إذا لم يكن هناك مستخدم مسجل
                })
                ->orderBy('created_at');

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
            $results = $query->paginate($perPage);
            return response()->json([
                'status' => 200,
                'message' => $results->isEmpty()
                    ? 'لم يتم العثور على اقتراحات مناسبة'
                    : 'تم العثور على اقتراحات مناسبة',
                'data' => $results->items(),
                'meta' => [
                    'total_pages' => $results->lastPage(),
                    'total' => $results->total(),
                    'count' => count($results->items()),
                    'current_page' => (int)$currentPage,
                    'per_page' => (int)$perPage
                ]
            ]);
        } catch (\Throwable $e) {
            return $this->returnError($e->getMessage(), 'حدث خطأ أثناء توليد الاقتراحات');
        }
    }
}
