<?php

namespace App\Services;

use App\GeneralTrait;
use App\Models\Favorite;
use App\Models\RoadSign;

class FavoriteService
{

    use GeneralTrait;

    public function getAll()
    {
        try {
            $favorite =  auth('customer')->user()->load([
                'favorite.roadSign',
                'favorite.roadSign.template',
                'favorite.roadSign.city',
                'favorite.roadSign.region'
            ]);
            return $this->returnData($favorite->favorite, 'تمت العملية بنجاح');
        } catch (\Throwable $e) {
            return $this->returnError($e->getCode(), $e->getMessage());
        }
    }

    public function add($request)
    {
        try {
            $user = auth('customer')->user();
            $favorite = RoadSign::where('id', $request->road_id)->first();
            if (!$favorite) {
                return $this->returnError(502, 'الإعلان غير موجود');
            }
            $exists = Favorite::where('customer_id', auth('customer')->id())
                ->where('road_id', $request->road_id)
                ->exists();
            if ($exists) {
                return $this->returnError(502, 'تمت إضافة الإعلان مسبقاً');
            }
            Favorite::create([
                'road_id' => $request->road_id,
                'customer_id' => $user->id
            ]);
            return $this->returnData(201, 'تم اضافة العنصر إلى المفضلة');
        } catch (\Throwable $e) {
            return $this->returnError($e->getCode(), $e->getMessage());
        }
    }
    public function delete($id)
    {
        try {
            $road = Favorite::find($id);
            if (!$road) {
                return $this->returnError(404, 'العنصر غير موجود');
            }
            $user = auth('customer')->user();
            if (!$user) {
                return $this->returnError(503, 'خطأ في المصداقة');
            }
            if ($road->customer_id != $user->id) {
                return $this->returnError(501, "إدخال خاطئ");
            }
            $road->delete();
            return $this->returnData(200, 'تم إزالة العنصر من المفضلة');
        } catch (\Throwable $e) {
            return $this->returnError($e->getCode(), $e->getMessage());
        }
    }
}
