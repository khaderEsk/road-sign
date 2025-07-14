<?php

namespace App\Services;

use App\Models\Region;
use App\Models\Activity;
use Illuminate\Support\Facades\Auth;

class RegionService extends Services
{
    public function getAll()
    {
        return Region::with(['city'])->orderbyDesc('created_at')->get();
    }

    public function getById($id)
    {
        return Region::with(['city'])->findOrFail($id);
    }

    public function create(array $data)
    {
        $region = Region::create($data);
        $this->logActivity("تم إنشاء المنطقة: " . $region->name . " بواسطة المستخدم: " . auth()->user()->username);
        return $region;
    }

    public function update($id, array $data)
    {
        $region = Region::findOrFail($id);
        $region->update($data);
        $this->logActivity("تم تحديث المنطقة: " . $region->name . " بواسطة المستخدم: " . auth()->user()->username);
        return $region;
    }

    public function delete($id)
    {
        $region = Region::findOrFail($id);
        $this->logActivity("تم حذف المنطقة: " . $region->name . " بواسطة المستخدم: " . auth()->user()->username);
        $region->delete();
        return true;
    }

    public function getActiveByCity($city_id)
    {
        return Region::query()->where('city_id',$city_id)->get();
    }
}
