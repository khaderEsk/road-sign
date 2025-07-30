<?php

namespace App\Services;

use App\GeneralTrait;
use App\Models\RoadSign;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class AIService extends Services
{
    use GeneralTrait;
    public function getAll(array $data)
    {
        $roadsignsQuery = RoadSign::with(['template', 'city', 'region', 'bookings' => function ($q) use ($data) {
            if (!empty($data['start_date'])) {
                $q->wherePivot('start_date', '<=', $data['end_date'])
                    ->wherePivot('end_date', '>=', $data['start_date']);
            }
        }])
            ->join('templates', 'road_signs.template_id', '=', 'templates.id')
            ->select('road_signs.*')
            ->orderBy('templates.size', 'desc');

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

        $perPage = $data['per_page'] ?? 10;

        $roadsigns = $roadsignsQuery->orderByDesc('created_at')->paginate($perPage);

        $roadsigns->getCollection()->transform(function ($roadSign) use ($data) {
            $roadSign->total_faces_on_date = $roadSign->bookings->sum('pivot.booking_faces');
            $roadSign->total_panels_on_date = $roadSign->bookings->sum('pivot.number_of_reserved_panels');

            if (!empty($data['start_date']) && !empty($data['end_date'])) {
                $ranges = self::calculateDateRanges($roadSign, $data['start_date'], $data['end_date']);
                $roadSign->available_date_ranges = $ranges['available_date_ranges'];
                $roadSign->booking_dates = $ranges['booking_dates'];
            } else {
                $roadSign->available_date_ranges = [];
                $roadSign->booking_dates = [];
            }

            return $roadSign;
        });

        return response()->json(
            [
                'status' => true,
                'errNum' => 200,
                'message' => $roadsigns->isEmpty()
                    ? 'لم يتم العثور على اقتراحات مناسبة'
                    : 'تم العثور على اقتراحات مناسبة',
                "data" =>  $roadsigns->items(),
                'meta' => [
                    'total_pages' => $roadsigns->lastPage(),
                    'total' => $roadsigns->total(),
                    'count' => count($roadsigns->items()),
                    'current_page' => (int)$data['page'],
                    'per_page' => (int)$perPage
                ]
            ]
        );
    }
    public static function calculateDateRanges(RoadSign $roadSign, $startDate, $endDate): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        $dailyReservedPanels = [];
        foreach ($roadSign->bookings as $booking) {
            $from = Carbon::parse($booking->pivot->start_date)->startOfDay();
            $to = Carbon::parse($booking->pivot->end_date)->endOfDay();
            $reserved = $booking->pivot->number_of_reserved_panels ?? 0;
            for ($date = $from->copy(); $date->lte($to); $date->addDay()) {
                $key = $date->toDateString();
                if (!isset($dailyReservedPanels[$key])) {
                    $dailyReservedPanels[$key] = 0;
                }
                $dailyReservedPanels[$key] += $reserved;
            }
        }
        $availableDateRanges = [];
        $currentStart = null;
        $currentAvailable = null;
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $key = $date->toDateString();
            $reserved = $dailyReservedPanels[$key] ?? 0;
            $available = max(0, $roadSign->panels_number - $reserved);
            if ($available <= 0) {
                if ($currentStart !== null) {
                    $availableDateRanges[] = [
                        'start_date' => $currentStart->toDateString(),
                        'end_date' => $date->copy()->subDay()->toDateString(),
                        'days' => $currentStart->diffInDays($date) <= 1 ? 1 : (int)$currentStart->diffInDays($date) + 1,
                        'available_panels' => $currentAvailable
                    ];
                    $currentStart = null;
                    $currentAvailable = null;
                }
                continue;
            }
            if ($currentStart === null) {
                $currentStart = $date->copy();
                $currentAvailable = $available;
            } elseif ($available !== $currentAvailable) {
                $availableDateRanges[] = [
                    'start_date' => $currentStart->toDateString(),
                    'end_date' => $date->copy()->subDay()->toDateString(),
                    'days' => $currentStart->diffInDays($date) <= 1 ? 1 : (int)$currentStart->diffInDays($date) + 1,
                    'available_panels' => $currentAvailable
                ];
                $currentStart = $date->copy();
                $currentAvailable = $available;
            }
        }
        if ($currentStart !== null) {
            $availableDateRanges[] = [
                'start_date' => $currentStart->toDateString(),
                'end_date' => $end->toDateString(),
                'days' => $currentStart->diffInDays($date) <= 1 ? 1 : (int)$currentStart->diffInDays($date) + 1,
                'available_panels' => $currentAvailable
            ];
        }
        $bookingRanges = collect($roadSign->bookings)->map(function ($booking) use ($start, $end) {
            $from = Carbon::parse($booking->pivot->start_date)->startOfDay();
            $to = Carbon::parse($booking->pivot->end_date)->endOfDay();
            $effectiveStart = $from->greaterThan($start) ? $from : $start;
            $effectiveEnd = $to->lessThan($end) ? $to : $end;
            return [
                'start_date' => $effectiveStart->toDateString(),
                'end_date' => $effectiveEnd->toDateString(),
                'days' => $effectiveStart->diffInDays($effectiveEnd) <= 1 ? 1 : (int)$effectiveStart->diffInDays($effectiveEnd) + 1,
                'panels_reserved' => (int) $booking->pivot->number_of_reserved_panels ?? 0
            ];
        })->toArray();

        return [
            'available_date_ranges' => $availableDateRanges,
            'booking_dates' => $bookingRanges
        ];
    }
}
