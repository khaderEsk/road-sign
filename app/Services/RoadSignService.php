<?php

namespace App\Services;

use App\GeneralTrait;
use App\Models\Customer;
use App\Models\RoadSign;
use App\Models\Template;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;

class RoadSignService extends Services
{
    use GeneralTrait;

    public function getAllRoadSing()
    {
        try {
            $customer = auth('customer')->user();
            $roadSigns = RoadSign::with([
                'region',
                'city',
                'template',
                'template.products',
            ])
                ->when($customer, function ($query) {
                    $query->join('cities', 'road_signs.city_id', '=', 'cities.id')
                        // $query->join('regions', 'road_signs.region_id', '=', 'regions.id')
                        ->orderBy('cities.name')
                        ->orderBy('road_signs.created_at');
                }, function ($query) {
                    $query->orderBy('road_signs.created_at');
                })
                ->get();
            return $this->returnData($roadSigns, 'تمت العملية بنجاح');
        } catch (\Throwable $e) {
            return $this->returnError($e->getTraceAsString(), $e->getMessage());
        }
    }

    public function RoadSingSites()
    {
        try {
            $roadSing = RoadSign::select('latitudeX', 'longitudeY')->get();
            return $this->returnData($roadSing, 'تمت العملية بنجاح');
        } catch (\Throwable $e) {
            return $this->returnError($e->getTraceAsString(), $e->getMessage());
        }
    }

    public function getById($id)
    {
        try {
            $roadSing = RoadSign::with([
                'region',
                'city',
                'template',
                'template.products'
            ])->find($id);
            if (!$roadSing) {
                return $this->returnError(404, 'الإعلان غير موجود، إدخال خاطئ');
            }
            return $this->returnData($roadSing, 'تمت العملية بنجاح');
        } catch (\Throwable $e) {
            return $this->returnError($e->getTraceAsString(), $e->getMessage());
        }
    }

    public function getRoadSingsFilter($request)
    {
        try {
            $roadSings = RoadSign::with([
                'region',
                'city',
                'template',
                'template.products'
            ])->where('city_id', $request->city_id)->where('region_id', $request->region_id)->get();
            if (!$roadSings) {
                return $this->returnError(404, 'الإعلان غير موجود، إدخال خاطئ');
            }
            return $this->returnData($roadSings, 'تمت العملية بنجاح');
        } catch (\Throwable $e) {
            return $this->returnError($e->getTraceAsString(), $e->getMessage());
        }
    }


    public function checkRoadSignIsAvilable(
        $road_sign_id,
        $start_date,
        $end_date,
        $booking_faces,
        $number_of_reserved_panels,
        $exclude_booking_id = null
    ) {
        $road = RoadSign::with('bookings')->find($road_sign_id);

        if (!$road) {
            throw new HttpResponseException(response()->json([
                'message' => 'اللوحة غير موجودة',
                'road_sign_id' => $road_sign_id,
            ], 404));
        }

        $query = $road->bookings()
            ->wherePivot('start_date', '<=', $end_date)
            ->wherePivot('end_date', '>=', $start_date);

        if ($exclude_booking_id) {
            $query->where('bookings.id', '!=', $exclude_booking_id);
        }

        $sum_booking_faces = $query->sum('booking_faces');
        $sum_number_of_reserved_panels = $query->sum('number_of_reserved_panels');

        $available_faces = $road->faces_number - $sum_booking_faces;
        $available_panels = $road->panels_number - $sum_number_of_reserved_panels;

        if ($number_of_reserved_panels > $available_panels) {
            throw new HttpResponseException(response()->json([
                'message' => "عدد اللوحات المطلوبة ({$number_of_reserved_panels}) أكثر من المتاح في الفترة المحددة. عدد لوحات المحجوزة حالياً: {$sum_number_of_reserved_panels} من أصل {$road->panels_number}",
                'road_sign_id' => $road_sign_id,
                'reserved_panels_in_period' => (int) $sum_number_of_reserved_panels,
                'available_panels_in_period' => $available_panels,
                'total_panels' => $road->panels_number,
            ], 400));
        }
        if ($number_of_reserved_panels > $road->panels_number) {
            throw new HttpResponseException(response()->json([
                'message' => "عدد الوحات المطلوبة ({$number_of_reserved_panels})أكبر من عدد الوحات الكلي للوجه الواحد",
                'road_sign_id' => $road_sign_id,
                'total_number_of_reserved_panels' => $road->panels_number,
            ], 400));
        }
    }

    public function getRoadsignsDontHaveBooking($data)
    {
        $roadsignsQuery = RoadSign::query()->with(['template', 'city', 'region']);

        $startDate = $data['from_date'] ?? Carbon::now()->startOfWeek(Carbon::SUNDAY)->startOfDay();
        $endDate = $data['to_date'] ?? Carbon::now()->startOfWeek(Carbon::SUNDAY)->addDays(6)->endOfDay();

        if (!empty($data['city_id'])) {
            $roadsignsQuery->where('city_id', $data['city_id']);
        }

        if (!empty($data['region_id'])) {
            $roadsignsQuery->where('region_id', $data['region_id']);
        }

        if (!empty($data['place'])) {
            $roadsignsQuery->where('place', "like", "%" . $data['place'] . "%");
        }

        if (!empty($data['model'])) {
            $roadsignsQuery->whereHas('template', function ($q) use ($data) {
                $q->where('model', $data['model']);
            });
        }
        return $roadsignsQuery->whereDoesntHave('bookings', function ($query) use ($startDate, $endDate) {
            $query->where('bookings.start_date', '<=', $endDate)
                ->where('bookings.end_date', '>=', $startDate);
        })->get();
    }


    public function getRoadsignsBookingByWeek($data)
    {
        $startOfWeek = $data['from_date'] ??  Carbon::now()->startOfWeek(Carbon::SUNDAY)->startOfDay();
        $endOfWeek = $data['to_date'] ?? Carbon::now()->startOfWeek(Carbon::SUNDAY)->addDays(6)->endOfDay();
        $roadsignsQuery = RoadSign::with(['template', 'city', 'region', 'bookings.customer']);

        if (!empty($data['city_id'])) {
            $roadsignsQuery->where('city_id', $data['city_id']);
        }

        if (!empty($data['region_id'])) {
            $roadsignsQuery->where('region_id', $data['region_id']);
        }

        if (!empty($data['place'])) {
            $roadsignsQuery->where('place', "like", "%" . $data['place'] . "%");
        }

        if (!empty($data['model'])) {
            $roadsignsQuery->whereHas('template', function ($q) use ($data) {
                return $q->where('model', $data['model']);
            });
        }

        return $roadsignsQuery->whereHas('bookings', function ($q) use ($startOfWeek, $endOfWeek) {
            $q->where(function ($query) use ($startOfWeek, $endOfWeek) {
                $query->where('bookings.start_date', '<=', $endOfWeek)
                    ->Where('bookings.end_date', '>=', $startOfWeek);
            });
        })->get();
    }

    public function getRoadSignsBookingsByCustomerWithTemplatesModel($data)
    {
        $startDate = $data['from_date'] ?? Carbon::now()->startOfWeek(Carbon::SUNDAY)->startOfDay();
        $endDate = $data['to_date'] ?? Carbon::now()->startOfWeek(Carbon::SUNDAY)->addDays(6)->endOfDay();
        $customersQuery = Customer::query();
        if (!empty($data['product_type'])) {
            $customersQuery->whereHas('bookings', function ($query) use ($data) {
                $query->where('bookings.product_type', $data['product_type']);
            });
        }

        $customers = $customersQuery->whereHas('bookings', function ($query) use ($startDate, $endDate) {
            $query->where('bookings.start_date', '<=', $endDate)
                ->where('bookings.end_date', '>=', $startDate);
        })
            ->with(['bookings.roadSigns' => function ($query) {
                $query->with(['template']);
            }])
            ->get();

        $customers = $customers->map(function ($customer) {
            $roadSigns = $customer->bookings
                ->flatMap(function ($booking) {
                    return $booking->roadSigns->map(function ($roadSign) use ($booking) {
                        $number_of_reserved_panels = $roadSign->pivot->number_of_reserved_panels ?? 1;
                        $roadSign->total_panels_on_date = $roadSign->pivot->number_of_reserved_panels;
                        $roadSign->total_availabel_panels_on_date = $roadSign->panels_number - $roadSign->pivot->number_of_reserved_panels;
                        $roadSign->total_advertising_space = ($roadSign->template->advertising_space ?? 0) * $number_of_reserved_panels;
                        return $roadSign;
                    });
                });

            $customer->total_advertising_space = $roadSigns->sum('total_advertising_space');
            $customer->roadSigns = $roadSigns;

            unset($customer->bookings);
            return $customer;
        });

        $totalAdvertisingSpaceAllCustomers = $customers->sum('total_advertising_space');

        $totalPanelsPerModel = RoadSign::select(
            'templates.model',
            DB::raw('SUM(road_signs.panels_number) as total_panels')
        )
            ->join('templates', 'road_signs.template_id', '=', 'templates.id')
            ->groupBy('templates.model')
            ->get()
            ->keyBy('model');

        $globalModelCounts = Template::select(
            'templates.model',
            DB::raw('SUM(booking_road_sign.number_of_reserved_panels) as reserved_panels'),
            DB::raw('SUM(templates.advertising_space * booking_road_sign.number_of_reserved_panels) as total_advertising_space')
        )
            ->join('road_signs', 'templates.id', '=', 'road_signs.template_id')
            ->join('booking_road_sign', 'road_signs.id', '=', 'booking_road_sign.road_sign_id')
            ->join('bookings', 'bookings.id', '=', 'booking_road_sign.booking_id')
            ->where('bookings.start_date', '<=', $endDate)
            ->where('bookings.end_date', '>=', $startDate)
            ->when(!empty($data['product_type']), function ($query) use ($data) {
                $query->where('bookings.product_type', $data['product_type']);
            })
            ->groupBy('templates.model')
            ->get()
            ->map(function ($item) use ($totalPanelsPerModel) {
                $model = $item->model;
                $item->reserved_panels = (int) $item->reserved_panels;
                $item->total_advertising_space = (float) $item->total_advertising_space;

                $item->total_panels = (int) ($totalPanelsPerModel[$model]->total_panels ?? 0);
                $item->available_panels = (int)$item->total_panels - $item->reserved_panels;
                return $item;
            })
            ->toArray();

        return [
            'customers' => $customers,
            'globalModelCounts' => $globalModelCounts,
            'total_advertising_space_all_customers' => $totalAdvertisingSpaceAllCustomers,
            'from_date' => $startDate,
            'to_date' => $endDate
        ];
    }
}