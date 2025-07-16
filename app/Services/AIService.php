<?php

namespace App\Services;

use App\GeneralTrait;
use App\Models\RoadSign;

class AIService extends Services
{
    use GeneralTrait;
    public function recommendByLocationAndBudget($request)
    {
        try {
            $budget = $request->input('budget');
            $cityId = $request->input('city_id');
            $regionId = $request->input('region_id');
            $type = $request->input('type');
            $roadSigns = RoadSign::with([
                'template.products',
                'city',
                'region',
            ])
                ->when($cityId, fn($q) => $q->where('city_id', $cityId))
                ->when($regionId, fn($q) => $q->where('region_id', $regionId))
                ->get()
                ->filter(function ($sign) use ($budget, $type) {
                    $products = $sign->template->products;
                    if ($type) {
                        $products = $products->filter(function ($product) use ($type) {
                            return $product->type->value === (int)$type
                                || $product->type->value === \App\ProductType::BOTH->value;
                        });
                    }
                    $totalCost = $products->sum('price');
                    if ($budget !== null) {
                        return $totalCost <= $budget;
                    }
                    return true;
                })
                ->sortBy(function ($sign) {
                    return $sign->template->products->sum('price');
                })
                ->values();
            if ($roadSigns->isEmpty()) {
                return $this->returnError(null, 'لا توجد اقتراحات مطابقة للمعايير التي أدخلتها.');
            }
            return $this->returnData($roadSigns, 'تم العثور على اقتراحات مناسبة');
        } catch (\Throwable $e) {
            return $this->returnError($e->getMessage(), 'حدث خطأ أثناء توليد الاقتراحات');
        }
    }
}
