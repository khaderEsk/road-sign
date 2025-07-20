<?php

namespace App\Services;

use App\Models\City;
use App\Models\Activity;
use Illuminate\Support\Facades\Auth;

class CityService extends Services
{
    public function getAll()
    {
        return City::with('regions')->orderbyDesc('created_at')->get();
    }

    public function getById($id)
    {
        return City::with('regions')->findOrFail($id);
    }

    public function create(array $data)
    {
        $city = City::create($data);
        $this->logActivity("تم إنشاء المدينة: " . $city->name . " بواسطة المستخدم: " . auth()->user()->username);        return $city;
    }

    public function update($id, array $data)
    {
        $city = City::findOrFail($id);
        $city->update($data);
        $this->logActivity("تم تحديث المدينة: " . $city->name . " بواسطة المستخدم: " . auth()->user()->username);
        return $city;
    }

    public function delete($id)
    {
        $city = City::findOrFail($id);
        $this->logActivity("تم حذف المدينة: " . $city->name . " بواسطة المستخدم: " . auth()->user()->username);
        $city->delete();
        return true;
    }

    public function getActive()
    {
        return City::query()->where('is_active', true)->get();
    }
}
