<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Broker;
use App\Models\Contract;
use App\Models\User;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Template;
use App\Models\RoadSign;

class DashboardService
{
    public function getCounts(): array
    {
        return [

            ['endpoint' => 'users', 'name' => 'الموظفين', 'count' => User::count()],
            ['endpoint' => 'customers', 'name' => 'الزبائن', 'count' => Customer::count()],
            ['endpoint' => 'models', 'name' => 'النماذج', 'count' => Template::count()],
            ['endpoint' => 'road_signs', 'name' => 'لوحات طرقية', 'count' => RoadSign::count()],
            ['endpoint' => 'payments', 'name' => 'الدفعات', 'count' => Payment::count()],
            // ['endpoint' => 'contracts', 'name' => 'العقود', 'count' => Contract::count()],
            ['endpoint' => 'brokers', 'name' => 'الوسيط', 'count' => Broker::count()],
            ['endpoint' => 'bookings', 'name' => 'الحجوزات', 'count' => Booking::count()],
            // ['endpoint' => 'orders', 'name' => 'الطلبات', 'count' => Order::count()],
        ];
    }
}
