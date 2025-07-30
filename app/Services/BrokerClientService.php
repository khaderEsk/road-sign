<?php

namespace App\Services;

use App\BalanceType;
use App\BookingType;
use App\CustomerType;
use App\DiscountType;
use App\GeneralTrait;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\RoadSign;
use App\Models\Transformation;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Exceptions\HttpResponseException;

class BrokerClientService extends Services
{
    use GeneralTrait;
    public function __construct(
        private OrderService $orderService,
        private RoadSignService $roadSignService,
        private PaymentService $paymentService
    ) {}

    public function getMyClient()
    {
        try {
            $broker = auth('broker')->user();
            if (!$broker) {
                return $this->returnError(400, 'لا تملك صلاحية دخول');
            }
            $customers = Customer::where('broker_id', $broker->id)->get();
            return $this->returnData($customers, 'تمت العملية بنجاح');
        } catch (\Throwable $e) {
            return $this->returnError($e->getCode(), $e->getMessage());
        }
    }

    public function store(array $data)
    {
        DB::beginTransaction();
        try {
            $broker = auth('broker')->user();
            $customer = Customer::create([
                ...$data,
                'broker_id' => $broker->id,
                'status' => 2
            ]);
            if (!empty($data['is_tracking']) && !empty($data['customer'])) {
                $trackingData = $data['customer'];
                $tracking = new Customer([
                    ...$trackingData,
                    'company_name' => $customer->company_name,
                    'type' => CustomerType::TRACKING,
                    'belong_id' => $customer->id,
                    'broker_id' => $broker->id,
                    'email' => $customer->email,
                    'password' => Hash::make(rand(10000, 99999)),
                    'status' => 2
                ]);
                $tracking->save();
            }
            DB::commit();
            $customer->load('customers');
            return $this->returnData($customer, 'تمت العملية بنجاح');
        } catch (Exception $e) {
            throw $e->getMessage();
            DB::rollBack();
        }
    }

    public function show($id)
    {
        try {
            $broker = auth('broker')->user();
            if (!$broker) {
                return $this->returnError(400, 'ليس لديك صلاحية');
            }
            $customer = Customer::with('customers', 'bookings', 'payments', 'discounts')
                ->where('id', $id)
                ->where('broker_id', $broker->id)
                ->first();
            if (!$customer) {
                return $this->returnError(400, 'ليس لديك صلاحية');
            }

            return $this->returnData($customer, 'تمت العملية بنجاح');
        } catch (\Throwable $th) {
            //throw $th;
        }
    }


    public function booking(array $data)
    {
        
        $ss = DB::transaction(function () use ($data) {
            $broker = auth('broker')->user();
            $amounts = $this->calculateAmount($data, $data['product_type']);
            $pivotData = $this->preparePivotData($data, $amounts['pricing_details']);
            
            $this->checkAvailability($pivotData, $data['start_date'], $data['end_date']);
            $booking = Booking::create($data);
            $booking->user_id = 2;
            $booking->broker_id = $broker->id;
            $booking->save();
            // return $booking;
            $booking->roadsigns()->sync($pivotData);
            $this->applyBookingAmounts($booking, $amounts);
            if ($booking->type->value === BookingType::PERMANENT->value) {
                $this->orderService->createInstallationAndReleaseOrders($booking);
                $this->updateCustomerBalance($booking);
                $booking;
            }
            return $booking->load('roadsigns');
        });
        return $ss;
    }

    public function calculateAmount(array $data, $productType): array
    {
        $totalPrice = 0;
        $pricingDetails = [];
        $total_advertising_space = 0;
        $total_price_befor_discount = 0;
        $total_printing_space = 0;
        foreach ($data['roadsigns'] as $item) {
            $roadSign = RoadSign::findOrFail($item['road_sign_id']);
            $product = $roadSign->template->products->firstWhere('type', $productType);
            $startDate = $item['start_date'];
            $endDate = $item['end_date'];
            $units = $this->calculateUnits($startDate, $endDate);
            $days_of_reservation = $this->calculateDaysBetween($startDate, $endDate);
            $total_faces_price = $product->price * $item['number_of_reserved_panels'] * $units;
            $pricingKey = $item['road_sign_id'] . '_' . $startDate . '_' . $endDate;
            $pricingDetails[$pricingKey] = [
                'face_price' => $product->price,
                'total_faces_price' => $total_faces_price,
                'days_of_reservation' => $days_of_reservation,
                'units' => $units,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ];
            $total_advertising_space += $roadSign->template->advertising_space * $item['number_of_reserved_panels'];
            $total_printing_space += $roadSign->template->printing_space * $item['number_of_reserved_panels'];
            $totalPrice += $total_faces_price;
        }
        $total_price_befor_discount =  $totalPrice;
        $total_price_per_month =  $totalPrice;
        if (isset($data['discount_type'])) {
            $total_price = $this->applyDiscount(
                $total_price_befor_discount,
                $data['discount_type'],
                $data['value']
            );
        } else {
            $total_price = $total_price_befor_discount;
        }
        return [
            'total_price_befor_discount' => $total_price_befor_discount,
            'total_price_per_month' => $total_price_per_month,
            'total_price' => $total_price,
            'total_advertising_space' => $total_advertising_space,
            'total_printing_space' => $total_printing_space,
            'pricing_details' => $pricingDetails,
            'units' => $this->calculateUnits($data['start_date'], $data['end_date'])
        ];
    }

    public function preparePivotData(array $data, array $pricingDetails = []): array
    {
        $roadsigns = collect($data['roadsigns']);
        $pivotData = [];
        foreach ($roadsigns as $roadsign) {
            $roadSignId = $roadsign['road_sign_id'];
            $startDate = $roadsign['start_date'];
            $endDate = $roadsign['end_date'];
            $pivotKey = $roadSignId . '_' . $startDate . '_' . $endDate;
            $pivotData[$pivotKey] = [
                'road_sign_id' => $roadSignId,
                'booking_faces' => $roadsign['booking_faces'],
                'number_of_reserved_panels' => $roadsign['number_of_reserved_panels'],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'face_price' => $pricingDetails[$pivotKey]['face_price'] ?? 0,
                'days_of_reservation' => $pricingDetails[$pivotKey]['days_of_reservation'] ?? 0,
                'units' => $pricingDetails[$pivotKey]['units'] ?? 0,
                'total_faces_price' => $pricingDetails[$pivotKey]['total_faces_price'] ?? 0,
            ];
        }
        return $pivotData;
    }

    private function checkAvailability(array $pivotData, string $startDate, string $endDate, int $booking_id = null): void
    {
        foreach ($pivotData as $pivot) {
            $this->roadSignService->checkRoadSignIsAvilable(
                $pivot['road_sign_id'],
                $pivot['start_date'],
                $pivot['end_date'],
                $pivot['booking_faces'],
                $pivot['number_of_reserved_panels'],
                $booking_id
            );
        }
    }


    private function applyBookingAmounts(Booking $booking, array $amounts): void
    {
        $booking->total_price = $amounts['total_price'];
        $booking->total_advertising_space = $amounts['total_advertising_space'];
        $booking->total_printing_space = $amounts['total_printing_space'];
        $booking->total_price_befor_discount = $amounts["total_price_befor_discount"];
        $booking->total_price_per_month = $amounts["total_price_per_month"];
        $booking->units = $amounts["units"];
        $booking->save();
    }

    private function updateCustomerBalance(Booking $booking, $type = BalanceType::INCREMENT, $originalPrice = 100): void
    {
        if ($type == BalanceType::INCREMENT) {
            $booking->customer->decrement('remaining', $originalPrice);
            $booking->customer->increment('remaining', $booking->total_price);
        } else {
            $booking->customer->decrement('remaining', $booking->total_price);
        }
    }

    private function calculateUnits($startDate, $endDate)
    {
        $days = $this->calculateDaysBetween($startDate, $endDate);
        return ceil($days / 28);
    }

    private function calculateDaysBetween(string $startDate, string $endDate): int
    {
        return Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
    }
    protected function applyDiscount(float $price, ?int $discountType, ?float $value): float
    {
        if (!$discountType || !$value || $value <= 0) {
            return $price;
        }
        if ($price < $value and $discountType == DiscountType::AMOUNT->value) {
            throw new HttpResponseException(response()->json([
                'message' => "قيمة الحسم اكبر من قيمة العقد"
            ], 422));
        }
        return match ($discountType) {
            1 => max(0, $price - $value),
            2 => max(0, $price - ($price * ($value / 100))),
            default => $price,
        };
    }

    public function calculateAmountBeforeBooking($data)
    {
        $amount = $this->calculateAmount($data, $data['product_type']);
        return [
            'amount' => $amount,
            'price_per_period' => $amount['total_price'],
            'units' => $this->calculateUnits($data['start_date'], $data['end_date'])
        ];
    }

    public function getPayment($id)
    {
        try {
            $broker = auth('broker')->user();
            $customer = Customer::with('payments')
                ->where('id', $id)
                ->where('broker_id', $broker->id)
                ->first();
            return $this->returnData($customer, 'تمت العملية بنجاح');
        } catch (\Throwable $e) {
            return $this->returnError($e->getCode(), $e->getMessage());
        }
    }

    public function payment(array $data)
    {
        DB::beginTransaction();
        try {
            $broker = auth('broker')->user();
            $customer = Customer::find($data['customer_id']);
            if (!$broker) {
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
            $payment->is_received = 0;
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

    public function profile()
    {
        try {
            $broker = auth('broker')->user();
            if (!$broker) {
                return $this->returnError(400, 'ليس لديك صلاحية');
            }
            return $this->returnData($broker, 'تمت العملية بنجاح');
        } catch (\Throwable $e) {
            return $this->returnError($e->getCode(), $e->getMessage());
        }
    }

    public function transformation()
    {
        try {
            $broker = auth('broker')->user();
            if (!$broker) {
                return $this->returnError(400, 'ليس لديك صلاحية');
            }
            $broker->transformation;
            $broker->makeHidden('roles');
            return $this->returnData($broker, 'تمت العملية بنجاح');
        } catch (\Throwable $e) {
            return $this->returnError($e->getCode(), $e->getMessage());
        }
    }
}
