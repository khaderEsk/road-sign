<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingRequest;
use App\Http\Requests\CalculateAmountRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Routing\Controllers\Middleware;

class BookingController extends Controller
{
    public function __construct(protected BookingService $bookingService)
    {
        return [
            'permission:view-bookings|create-bookings|edit-bookings|delete-bookings',
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('delete-bookings'), only: ['destroy']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('view-bookings'), only: ['index', 'show']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('edit-bookings'), only: ['update']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('create-bookings'), only: ['store']),
        ];
    }

    public function index()
    {
        return response()->json($this->bookingService->getAll());
    }

    public function store(BookingRequest $request)
    {
        return response()->json($this->bookingService->create($request->validated()));
    }

    public function show($id)
    {
        return response()->json($this->bookingService->getById($id));
    }

    public function update(UpdateBookingRequest $request, $id)
    {
        return response()->json($this->bookingService->update($id, $request->validated()));
    }

    public function destroy($id)
    {
        return response()->json(['deleted' => $this->bookingService->delete($id)]);
    }
    public function calculateAmounts(CalculateAmountRequest $request)
    {
        return response()->json(
            $this->bookingService->calculateAmountBeforeBooking($request->validated())
        );
    }
}
