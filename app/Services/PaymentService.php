<?php

namespace App\Services;

use App\BookingType;
use App\ImageTrait;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Customer;
use App\PaymentIsReceived;
use Illuminate\Support\Facades\DB;

class PaymentService extends Services
{
    use ImageTrait;

    public function getAll($data)
    {
        $payments = Payment::query()->with(['user', 'customer']);
        if (!empty($data['from_date'])) {
            $payments->where('date', '>=', $data['from_date']);
        }
        if (!empty($data['end_date'])) {
            $payments->where('date', '<=', $data['end_date']);
        }

        if (!empty($data['is_received'])) {
            $value = $data['is_received'];
            $boolValue = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            $payments->where('is_received', $boolValue);
        }

        if (!empty($data['customer_id'])) {
            $payments->where('customer_id', $data['customer_id']);
        }

        if (!empty($data['user_id'])) {
            $payments->where('user_id', $data['user_id']);
        }
        return $payments->orderByDesc('date')->get();
    }

    public function getTotalPaymentAndRemaining($data)
    {

        $payments =  Payment::where('is_received', true);
        if (isset($data['from_date']) && isset($data['to_date'])) {
            $payments->whereBetween('date', [$data['from_date'], $data['to_date']]);
        };
        $results = [
            'total_paid_received' => (float) $payments->sum('paid'),
            'total_customer_remaining' => (float) Customer::sum('remaining'),
            'total_booking_amount' => (float)Booking::where('type', BookingType::PERMANENT)
                ->sum('total_price'),

        ];

        return $results;
    }

    public function getById($id)
    {
        return Payment::with(['user', 'customer'])->findOrFail($id);
    }

    public function create(array $data)
    {
        DB::beginTransaction();

        try {
            $customer = Customer::find($data['customer_id']);
            if (!$customer) {
                throw new \Exception('Customer not found');
            }

            $payment = new Payment();
            $payment->customer_id = $customer->id;
            $payment->user_id = Auth()->id();
            $payment->paid = $data['paid'];
            $payment->total = $customer->remaining;
            $payment->remaining = $payment->total - $data['paid'];
            $payment->payment_number = $data['payment_number'];
            $payment->payment_image = $data['payment_image'];
            $payment->date = now();
            $payment->save();

            $customer->remaining = $payment->remaining;
            $customer->save();

            $this->logActivity('تم إنشاء دفعة للعميل: ' . $payment->customer->company_name . " بواسطة المستخدم: " . auth()->user()->username);

            DB::commit();

            return  $payment;
        } catch (\Exception $e) {
            DB::rollBack();
            Log('Erorr', $e->getMessage());
            return 'Failed to create payment: ';
        }
    }

    public function update($id, array $data)
    {
        $payment = Payment::findOrFail($id);
        $payment->paid = $data['paid'];
        $payment->remaining = $payment->remaining - $data['paid'];
        $payment->payment_number = $data['payment_number'];
        $payment->payment_image = $data['payment_image'];
        $payment->date = now();
        $payment->save();
        $this->logActivity('تم تحديث الدفعة للعميل: ' . $payment->customer->company_name . " بواسطة المستخدم: " . auth()->user()->username);
        return $payment;
    }

    public function delete($id)
    {
        $payment = Payment::findOrFail($id);
        $this->logActivity('تم حذف الدفعة للعميل: ' . $payment->customer->company_name . " بواسطة المستخدم: " . auth()->user()->username);
        $payment->delete();

        return true;
    }
    public function IsReceived($payment_id)
    {
        $payment = Payment::findOrFail($payment_id);
        $payment->is_received = PaymentIsReceived::RECEIVED;
        $payment->save();
        return $payment;
    }
}
