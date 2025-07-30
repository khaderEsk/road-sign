<?php

namespace App\Http\Controllers\Api\V1\Broker;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingBrokerRequest;
use App\Http\Requests\BookingRequest;
use App\Http\Requests\CalculateAmountRequest;
use App\Http\Requests\CustomerRequest;
use App\Http\Requests\PaymentBrokerRequest;
use App\Http\Requests\PaymentRequest;
use App\ImageTrait;
use App\Services\BrokerClientService;

class BrokerClientController extends Controller
{
    use ImageTrait;
    public function __construct(protected BrokerClientService $brokerClientService) {}
    public function index()
    {
        return $this->brokerClientService->getMyClient();
    }

    public  function store(CustomerRequest $request)
    {
        return $this->brokerClientService->store($request->validated());
    }

    public  function booking(BookingBrokerRequest $request)
    {
        
        return $this->brokerClientService->booking($request->validated());
    }


    public function show($id)
    {
        return $this->brokerClientService->show($id);
    }
    public function getPayment($id)
    {
        return $this->brokerClientService->getPayment($id);
    }

    public function payment(PaymentBrokerRequest $request)
    {
        $data = $request->validated();

        $data['payment_image'] = $this->uploadImage($request, 'payment_image', 'payments');
        return $this->brokerClientService->payment($data);
    }

    public function profile()
    {
        return $this->brokerClientService->profile();
    }

    public function transformation()
    {
        return $this->brokerClientService->transformation();
    }

    public function calculateAmounts(CalculateAmountRequest $request)
    {
        return response()->json(
            $this->brokerClientService->calculateAmountBeforeBooking($request->validated())
        );
    }
}
