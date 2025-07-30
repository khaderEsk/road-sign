<?php

namespace App\Services;

use App\BalanceType;
use App\BookingType;
use App\DiscountType;
use App\GeneralTrait;
use App\Models\Booking;
use App\Models\RoadSign;
use Carbon\Carbon;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;

class BookingCustomerService extends Services
{
    use GeneralTrait;
    public function __construct(
        private OrderService $orderService,
        private RoadSignService $roadSignService,
        private PaymentService $paymentService
    ) {}

    public function getAll()
    {
        try {
            $customer = auth('customer')->user();
            if (!$customer) {
                return $this->returnError(503, 'خطأ في المصادقة');
            }
            if ($customer->status == 1) {
                return $this->returnData(['status' => $customer->status], 'طلبك قيد المعالجة');
            } elseif ($customer->status == 0) {
                return $this->returnData(['status' => $customer->status], 'يجب تأكيد حسابك');
            }
            $customer->load(['bookings.roadSigns', 'bookings.roadSigns.city', 'bookings.roadSigns.region', 'bookings.roadSigns.template', 'bookings.roadSigns.template.products']);
            $bookings = $customer->getRelation('bookings');
            return $this->returnData($bookings, 'تمت العملية بنجاح');
        } catch (\Throwable $e) {
            return $this->returnError($e->getCode(), $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $customer = auth('customer')->user();
            if (!$customer) {
                return $this->returnError(503, 'خطأ في المصادقة');
            }
            $booking = Booking::with([
                'roadSigns.city',
                'roadSigns.region',
                'roadSigns.template.products',
                'customer'
            ])->find($id);
            if (!$booking) {
                return $this->returnError(404, 'الحجز غير موجود');
            }
            if ($booking->customer_id != $customer->id) {
                return $this->returnError(404, 'الحجز ليس لك');
            }
            foreach ($booking->roadsigns as $roadSign) {
                $roadSign->total_faces_on_date = $roadSign->bookings->sum(function ($relatedBooking) {
                    return $relatedBooking->pivot->booking_faces ?? 0;
                });
                $roadSign->total_panels_on_date = $roadSign->bookings->sum(function ($relatedBooking) {
                    return $relatedBooking->pivot->number_of_reserved_panels ?? 0;
                });
            }
            $startDate = $booking->start_date;
            $endDate = $booking->end_date;
            $groupedTemplates = $booking->roadsigns
                ->groupBy(function ($roadSign) {
                    return $roadSign->template->model ?? 'unknown';
                })
                ->map(function ($group, $model) use ($startDate, $endDate) {
                    $totalFaces = $group->sum(function ($roadSign) use ($startDate, $endDate) {
                        return $roadSign->bookings
                            ->filter(function ($relatedBooking) use ($startDate, $endDate) {
                                return $relatedBooking->start_date <= $endDate &&
                                    $relatedBooking->end_date >= $startDate;
                            })
                            ->sum(function ($relatedBooking) {
                                return $relatedBooking->pivot->booking_faces ?? 0;
                                return $relatedBooking->pivot->number_of_reserved_panels ?? 0;
                            });
                    });
                    return [
                        'model' => $model,
                        'total_faces' => $totalFaces,
                    ];
                })
                ->values();
            $booking->groupedTemplates = $groupedTemplates;


            return $this->returnData($booking, 'تمت العملية بنجاح');
        } catch (\Throwable $e) {
            return $this->returnError($e->getCode(), $e->getMessage());
        }
    }

    public function update($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $customer = auth('customer')->user();
            if (!$customer) {
                return $this->returnError(404, 'حدث خطأ');
            }
            $booking = Booking::find($id);
            if (!$booking) {
                return $this->returnError(404, 'الحجز غير موجود');
            }
            if ($booking->customer_id != $customer->id) {
                return $this->returnError(404, 'ليس لديك صلاحية تعديل');
            }
            $originalType = $booking->type;
            $originalPrice = $booking->total_price;
            $amounts = $this->calculateAmount($data, $data['product_type']);
            $pivotData = $this->preparePivotData($data, $amounts['pricing_details']);
            $this->checkAvailability($pivotData, $data['start_date'], $data['end_date'], $id);
            $booking->update($data);
            $booking->roadsigns()->sync($pivotData);
            $this->applyBookingAmounts($booking, $amounts);
            if ($originalType->value != $data['type'] && $data['type'] == BookingType::PERMANENT->value) {
                $this->orderService->createInstallationAndReleaseOrders($booking);
                $this->updateCustomerBalance($booking, BalanceType::INCREMENT, $originalPrice);
            }
            if ($originalType->value  != $data['type'] && $data['type'] == BookingType::TEMPORARY->value) {
                $this->updateCustomerBalance($booking, BalanceType::DECREMENT, $originalPrice);
                $booking->orders()->delete();
            }
            return $booking->load('roadsigns');
        });
    }

    public function delete($id)
    {
        try {
            $customer = auth('customer')->user();
            if (!$customer) {
                return $this->returnError(404, 'حدث خطأ');
            }
            $booking = Booking::find($id);
            if (!$booking) {
                return $this->returnError(404, 'الحجز غير موجود');
            }
            if ($booking->customer_id != $customer->id) {
                return $this->returnError(503, 'ليس لديك صلاحية حذف');
            }
            $booking->delete();
            return $this->returnData(200, 'تم إزالة الحجز بنجاح');
        } catch (\Throwable $e) {
            return $this->returnError($e->getCode(), $e->getMessage());
        }
        // $this->logActivity("تم حذف الحجز للعميل: {$booking->customer->full_name} بواسطة: {$booking->user->full_name}");

        return true;
    }

    public function getById($id)
    {
        $booking = Booking::with(['user', 'user.company', 'roadsigns', 'roadsigns.city', 'roadsigns.region', 'customer', 'roadsigns.template'])->findOrFail($id);
        foreach ($booking->roadsigns as $roadSign) {
            $roadSign->total_faces_on_date = $roadSign->bookings->sum(function ($relatedBooking) {
                return $relatedBooking->pivot->booking_faces ?? 0;
            });
            $roadSign->total_panels_on_date = $roadSign->bookings->sum(function ($relatedBooking) {
                return $relatedBooking->pivot->number_of_reserved_panels ?? 0;
            });
        }
        $startDate = $booking->start_date;
        $endDate = $booking->end_date;
        $groupedTemplates = $booking->roadsigns
            ->groupBy(function ($roadSign) {
                return $roadSign->template->model ?? 'unknown';
            })
            ->map(function ($group, $model) use ($startDate, $endDate) {
                $totalFaces = $group->sum(function ($roadSign) use ($startDate, $endDate) {
                    return $roadSign->bookings
                        ->filter(function ($relatedBooking) use ($startDate, $endDate) {
                            return $relatedBooking->start_date <= $endDate &&
                                $relatedBooking->end_date >= $startDate;
                        })
                        ->sum(function ($relatedBooking) {
                            return $relatedBooking->pivot->booking_faces ?? 0;
                            return $relatedBooking->pivot->number_of_reserved_panels ?? 0;
                        });
                });
                return [
                    'model' => $model,
                    'total_faces' => $totalFaces,
                ];
            })
            ->values();
        $booking->groupedTemplates = $groupedTemplates;
        return $booking;
    }

    public function create(array $data)
    {
        $ss = DB::transaction(function () use ($data) {
            $amounts = $this->calculateAmount($data, $data['product_type']);
            $pivotData = $this->preparePivotData($data, $amounts['pricing_details']);
            $this->checkAvailability($pivotData, $data['start_date'], $data['end_date']);
            $booking = Booking::create($data);
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

    private function updateCustomerBalance(Booking $booking, $type = BalanceType::INCREMENT, $originalPrice = 100): void
    {
        if ($type == BalanceType::INCREMENT) {
            $booking->customer->decrement('remaining', $originalPrice);
            $booking->customer->increment('remaining', $booking->total_price);
        } else {
            $booking->customer->decrement('remaining', $booking->total_price);
        }
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

    private function calculateDaysBetween(string $startDate, string $endDate): int
    {
        return Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
    }

    private  function calculateBookingPriceFromDates(string $startDate, string $endDate, float $price): float
    {
        $days = $this->calculateDaysBetween($startDate, $endDate);
        $units = ceil($days / 28);
        return $units * $price;
    }

    private function calculateUnits($startDate, $endDate)
    {
        $days = $this->calculateDaysBetween($startDate, $endDate);
        return ceil($days / 28);
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
}
