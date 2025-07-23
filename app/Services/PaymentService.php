<?php

namespace App\Services;

use App\BookingType;
use App\GeneralTrait;
use App\ImageTrait;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Customer;
use App\PaymentIsReceived;
use Illuminate\Support\Facades\DB;

class PaymentService extends Services
{
    use ImageTrait;
    use GeneralTrait;
    public function getAll()
    {
        $customer = auth('customer')->user();
        $received = request()->input('is_received');
        if (!$customer) {
            return $this->returnError(404, 'الحساب غير موجود');
        }
        $received = filter_var(request()->input('is_received'), FILTER_VALIDATE_BOOLEAN);

        $payments = $customer->payments()
            ->when($received, function ($query) use ($received) {
                return $query->where('is_received', $received);
            })
            ->orderBy('created_at', 'desc')
            ->get();
        $message = isset($received)
            ? ($received ? 'الدفعات المستلمة' : 'الدفعات الغير مستلمة')
            : 'جميع الدفعات';

        return $this->returnData(
            $payments,
            $message
        );
    }



    public function create(array $data)
    {
        DB::beginTransaction();
        try {
            $customer = auth('customer')->user();
            if (!$customer) {
                return $this->returnError(404, 'الحساب غير موجود');
            }
            $previousPaymentsCount = Payment::where('customer_id', $customer->id)->count();
            $nextPaymentNumber = $previousPaymentsCount + 1;
            $payment = new Payment();
            $payment->customer_id = $customer->id;
            $payment->user_id = 2;
            $payment->paid = $data['paid'];
            $payment->total = $customer->remaining;
            $payment->remaining = $payment->total - $data['paid'];
            $payment->payment_number = $nextPaymentNumber;
            $payment->payment_image = $data['payment_image'];
            $payment->date = now();
            $payment->is_received= 0;
            $payment->save();
            $customer->remaining = $payment->remaining;
            $customer->save();
            DB::commit();
            return $this->returnData($payment, 'تمت العملية بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->returnError($e->getCode(), $e->getMessage());
        }
    }

    public function update($id, array $data)
    {
        $customer = auth('customer')->user();
        if (!$customer) {
            return $this->returnError(404, 'الحساب غير موجود');
        }
        $payment = Payment::find($id);
        if (!$payment) {
            return $this->returnError(404, 'الدفعة غير موجودة');
        }
        if ($payment->customer_id != $customer->id) {
            return $this->returnError(501, 'ليس لديك صلاحية تعديل هذه الدفعة');
        }
        if (!$payment->is_received == true) {
            return $this->returnError(503, 'الدفعة تم تأكيدها لا يمكن تعديلها');
        }
        $payment->paid = $data['paid'];
        $payment->remaining = $payment->remaining - $data['paid'];
        $payment->payment_number = $data['payment_number'];
        $payment->payment_image = $data['payment_image'];
        $payment->date = now();
        $payment->save();
        return $this->returnData($payment, 'تم تعديل الدفعة بنجاح');
    }

    public function delete($id)
    {
        $customer = auth('customer')->user();
        if (!$customer) {
            return $this->returnError(404, 'الحساب غير موجود');
        }
        $payment = Payment::find($id);
        if (!$payment) {
            return $this->returnError(404, 'الدفعة غير موجودة');
        }
        if ($payment->customer_id != $customer->id) {
            return $this->returnError(501, 'ليس لديك صلاحية تعديل هذه الدفعة');
        }
        if (!$payment->is_received == true) {
            return $this->returnError(503, 'الدفعة تم تأكيدها لا يمكن تعديلها');
        }
        $payment->delete();
        return $this->returnData(200, 'تم حذف الدفعة بنجاح');
    }

    public function IsReceived($payment_id)
    {
        $payment = Payment::findOrFail($payment_id);
        $payment->is_received = PaymentIsReceived::RECEIVED;
        $payment->save();
        return $payment;
    }
}
