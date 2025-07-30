<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingCustomerRequest;
use App\Http\Requests\CalculateAmountRequest;
use App\Http\Requests\UpdatedBookingCustomerRequest;
use App\Services\BookingCustomerService;
use Illuminate\Http\Request;

class BookingCustomerController extends Controller
{

    public function __construct(protected BookingCustomerService $bookingCustomerService) {}

    public function index()
    {
        return $this->bookingCustomerService->getAll();
    }

    public function create()
    {
        //
    }

    public function store(BookingCustomerRequest $request)
    {
        
        return response()->json($this->bookingCustomerService->create($request->validated()));
    }

    public function show($id)
    {
        return $this->bookingCustomerService->show($id);
    }

    public function edit(string $id)
    {
        //
    }

    public function update(UpdatedBookingCustomerRequest $request, $id)
    {
        return response()->json($this->bookingCustomerService->update($id, $request->validated()));
    }

    public function destroy($id)
    {
        return $this->bookingCustomerService->delete($id);
    }

    public function calculateAmounts(CalculateAmountRequest $request)
    {
        return response()->json(
            $this->bookingCustomerService->calculateAmountBeforeBooking($request->validated())
        );
    }
}
