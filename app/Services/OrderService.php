<?php

namespace App\Services;

use App\BookingStatus;
use App\Models\Order;
use App\Models\Booking;
use App\OrderType;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class OrderService extends Services
{
    public function getAll(array $data)
    {
        $orders = Order::with(['roadSign.region', 'roadSign.city', 'roadSign', 'customer', 'customer', 'customerNew'])
            ->when(isset($data['type']), function ($query) use ($data) {
                $query->where('type', $data['type']);
            })
            ->when(isset($data['city_id']), function ($query) use ($data) {
                $query->whereHas('roadSign.city', function ($q) use ($data) {
                    $q->where('id', $data['city_id']);
                });
            })
            ->when(isset($data['region_id']), function ($query) use ($data) {
                $query->whereHas('roadSign.region', function ($q) use ($data) {
                    $q->where('id', $data['region_id']);
                });
            })
            ->when(isset($data['action_date']), function ($query) use ($data) {
                $query->whereDate('action_date', $data['action_date']);
            })
            ->when(isset($data['order_execution_date']), function ($query) use ($data) {
                $query->whereDate('order_execution_date', $data['order_execution_date']);
            })
            ->when(isset($data['status']), function ($query) use ($data) {
                $value = $data['status'];
                $boolValue = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                $query->where('status', $boolValue);
            })
            ->when(isset($data['action_filter']), function ($query) use ($data) {

                $query->orderByDesc('action_date', 'id');
            })
            ->when(isset($data['execution_filter']), function ($query) use ($data) {

                $query->orderByDesc('order_execution_date', 'id');
            })
            ->get();

        return $orders;
    }



    public function getById($id)
    {
        return Order::with(['user', 'customer', 'roadSign'])->findOrFail($id);
    }

    public function create(array $data)
    {
        $order = Order::create($data);
        // $this->logActivity('Created order ID: ' . $order->id);
        return $order;
    }

    public function update($id, array $data)
    {
        $order = Order::findOrFail($id);

        $order->update([
            'action_date' => $data['action_date'],
            'notes' => $data['notes']
        ]);
        return $order;
    }

    public function updateStatus($id, bool $status)
    {
        DB::beginTransaction();
        try {
            $order = Order::findOrFail($id);
            $order->status = $status;
            $order->order_execution_date = Carbon::now();
            $order->save();
            $this->checkBookingStatus($order->booking);
            DB::commit();
            return $order;
        } catch (Exception $e) {
            throw $e->getMessage();
            DB::rollBack();
        }
    }

    public function delete($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();
        // $this->logActivity('Deleted order ID: ' . $order->id);
        return true;
    }

    public function createOrderBooking(
        $user_id,
        $order_type,
        $customer_id,
        $road_sign_id,
        $booking_id,
        $action_date
    ) {
        $order = new Order();
        $order->user_id = $user_id;
        $order->type = $order_type;
        $order->customer_id = $customer_id;
        $order->road_sign_id = $road_sign_id;
        $order->action_date = $action_date;
        $order->booking_id = $booking_id;
        $order->save();
    }
    public function createInstallationAndReleaseOrders(Booking $booking): void
    {
        foreach ($booking->roadsigns as $roadsign) {
            $startDate = Carbon::parse($roadsign->pivot->start_date);
            $endDate = Carbon::parse($roadsign->pivot->end_date);
            $order = Order::query()
                ->where('type', OrderType::RELEASE)
                ->where('road_sign_id', $roadsign->id)
                ->whereDate('action_date', $startDate->copy()->subDay());

            if ($order->exists()) {
                $orderChangeStatus =  $order->first();
                $orderChangeStatus->update([
                    "type" => OrderType::RELEASE_AND_INSTALLATION,
                    "customer_new_id" => $booking->customer_id,
                    "booking_new_id" => $booking->id
                ]);
            } else {
                $this->createOrderBooking(
                    $booking->user_id,
                    OrderType::INSTALLATION,
                    $booking->customer_id,
                    $roadsign->id,
                    $booking->id,
                    $startDate->copy()->subDay()
                );
            }


            $this->createOrderBooking(
                $booking->user_id,
                OrderType::RELEASE,
                $booking->customer_id,
                $roadsign->id,
                $booking->id,
                $endDate->copy()->addDay()
            );
        }
    }
    public function checkBookingStatus($booking)
    {
        $orders = $booking->orders();
        $orderInstaled = $orders->where('type', OrderType::INSTALLATION)->where('status', 1)->count();
        $orderoInstallation = $orders->where('type', OrderType::INSTALLATION)->count();
        if ($orderInstaled == $orderoInstallation && $booking->status == BookingStatus::PENDING->value) {
            $booking->update(['status' => BookingStatus::INSTALLED]);
        }
        $orderReleased = $orders->where('type', OrderType::RELEASE)->where('status', 1)->count();
        $orderoRelease = $orders->where('type', OrderType::RELEASE)->count();

        if ($orderReleased == $orderoRelease && $booking->status == BookingStatus::INSTALLED->value) {
            $booking->update(['status' => BookingStatus::COMPLETED]);
        }

        $orderReleasedAndInstaled = $orders->where('type', OrderType::RELEASE_AND_INSTALLATION)->where('status', 1)->count();
        $orderoReleaseAndInstalation = $orders->where('type', OrderType::RELEASE_AND_INSTALLATION)->count();
        if ($orderReleasedAndInstaled == $orderoReleaseAndInstalation && $booking->status == BookingStatus::INSTALLED->value) {
            $booking->update(['status' => BookingStatus::INSTALLED]);
        }
    }
}
