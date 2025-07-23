<?php

namespace App\Services;

use App\CustomerType;
use App\Mail\CustomerVerificationEmail;
use App\Models\Customer;
use App\Models\Activity;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class CustomerService extends Services
{
    public function getAll()
    {
        return Customer::with('customers')->where('type', CustomerType::OWNER)->orderbyDesc('created_at')->get();
    }

    public function getById($id)
    {
        $customer =  Customer::with('customers', 'bookings', 'payments', 'payments.user', 'discounts')->findOrFail($id);
        return  $customer;
    }

    public function create(array $data)
    {
        DB::beginTransaction();
        try {
            $customer = Customer::create($data);
            $otpCode = rand(100000, 999999);
            if (!empty($data['is_tracking'])) {
                $customerTracking = new Customer($data['customer']);
                $customerTracking->company_name = $customer->company_name;
                $customerTracking->type = CustomerType::TRACKING;
                $customerTracking->belong_id = $customer->id;
                $customerTracking->save();
            }
            $customer->otp_code = $otpCode;
            $customer->otp_expires_at = now()->addMinutes(10);
            $customer->save();
            Mail::to($customer->email)->send(new CustomerVerificationEmail([
                'name' => $customer->name,
                'otp' => $otpCode,
                'company_name' => $customer->company_name
            ]));
            DB::commit();
        } catch (Exception $e) {
            throw $e->getMessage();
            DB::rollBack();
        }

        $this->logActivity('تم إنشاء العميل: ' . $customer->full_name . " بواسطة المستخدم: " . auth()->user()->username);
        return $customer->load('customers');
    }

    public function update($id, array $data)
    {
        DB::beginTransaction();
        try {
            $customer = Customer::findOrFail($id);
            $customer->update($data);
            $customerTracking = $customer->customers()->first();
            if ($data['is_tracking'] == 1) {
                if (isset($customerTracking)) {
                    $customerTracking->update($data['customer']);
                } else {
                    $customerTracking = new Customer($data['customer']);
                    $customerTracking->company_name = $customer->company_name;
                    $customerTracking->type = CustomerType::TRACKING;
                    $customerTracking->belong_id = $customer->id;
                    $customerTracking->save();
                }
            } else {
                $customerTracking->delete();
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return $e->getMessage();
        }
        $this->logActivity('تم تحديث العميل: ' . $customer->full_name . " بواسطة المستخدم: " . auth()->user()->username);
        return $customer;
    }

    public function delete($id)
    {
        $customer = Customer::findOrFail($id);
        $this->logActivity('تم حذف العميل: ' . $customer->full_name . " بواسطة المستخدم: " . auth()->user()->username);
        $customer->customers()->delete();
        $customer->delete();

        return true;
    }
}
