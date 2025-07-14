<?php

namespace App\Services;

use App\Models\Broker;

class BrokerService extends Services
{
    public function getAll()
    {
        return Broker::orderbyDesc('created_at')->get();
    }

    public function getById($id)
    {
        return Broker::findOrFail($id);
    }

    public function create(array $data)
    {
        $broker = Broker::create($data);
        $this->logActivity('تم إنشاء الوسيط: ' . $broker->full_name . " بواسطة المستخدم: " . auth()->user()->username);
        return $broker;
    }

    public function update($id, array $data)
    {
        $broker = Broker::findOrFail($id);
        $broker->update($data);
        $this->logActivity('تم تحديث الوسيط: ' . $broker->full_name . " بواسطة المستخدم: " . auth()->user()->username);
        return $broker;
    }

    public function delete($id)
    {
        $broker = Broker::findOrFail($id);
        $this->logActivity('تم حذف الوسيط: ' . $broker->full_name . " بواسطة المستخدم: " . auth()->user()->username);
        $broker->delete();
        return true;
    }

}
