<?php

namespace App\Services;

use App\DiscountType;
use App\Models\Payment;
use App\Models\Customer;
use App\Models\Discount;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;

class DiscountService extends Services
{

    public function create($data)
    {
        DB::beginTransaction();

        try {
            $customer = Customer::find($data['customer_id']);
            if (!$customer) {
                throw new \Exception('الزبون غير موجود');
            }


            $discount = new Discount();
            $discount->customer_id = $customer->id;
            $discount->user_id = Auth()->id();
            $discount->value = $data['value'];
            $discount->total = $customer->remaining;
            $discount->discount_type = $data['discount_type'];
            $discount->remaining = $this->applyDiscount(
                $customer->remaining,
                $data['discount_type'],
                $data['value']
            );
            $discount->save();

            $customer->remaining = $discount->remaining;
            $customer->save();

            $this->logActivity('Created discount for customer: ' . $discount->customer->company_name . " by " . auth()->user()->username);

            DB::commit();

            return  $discount;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new HttpResponseException(response()->json([
                'message' => "لا يوجد رصيد لتنفيذ عملية الحسم"
            ], 422));
        }
    }

    protected function applyDiscount(float $price, ?int $discountType, ?float $value): float
    {
        if ($price == 0  || $price == null) {
            throw new HttpResponseException(response()->json([
                'message' => "لا يوجد رصيد لتنفيذ عملية الحسم"
            ], 422));
        }

        if (!$discountType || !$value || $value <= 0) {
            return 0;
        }
        if ($price < $value and $discountType == DiscountType::AMOUNT->value) {
            throw new HttpResponseException(response()->json([
                'message' => "قيمة الحسم اكبر من قيمة الموجوده"
            ], 422));
        }

        return match ($discountType) {
            1 => max(0, $price - $value),
            2 => max(0, $price - ($price * ($value / 100))),
            default => $price,
        };
    }
}
