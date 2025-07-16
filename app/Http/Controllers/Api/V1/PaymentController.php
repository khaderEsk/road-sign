<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentRequest;
use App\ImageTrait;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;

class PaymentController extends Controller
{
    use ImageTrait;
    public function __construct(protected PaymentService $paymentService) {}

    public function index()
    {
        return $this->paymentService->getAll();
    }

    public function getPaymentsUnaccepted()
    {
        return $this->paymentService->getPaymentsUnaccepted();
    }

    public function getPaymentsAccepted()
    {
        return $this->paymentService->getPaymentsAccepted();
    }

    public function store(PaymentRequest $request)
    {
        $data = $request->validated();
        $data['payment_image'] = $this->uploadImage($request, 'payment_image', 'payments');
        return response()->json($this->paymentService->create($data));
    }


    public function update(Request $request, $id)
    {
        return "yes";
        return $this->paymentService->update($id, $request->validated());
    }

    public function destroy($id)
    {
        return $this->paymentService->delete($id);
    }

    public function getTotalPaymentAndRemaining(Request $request)
    {
        $validated = $request->validate([
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date'
        ]);
        return response()->json($this->paymentService->getTotalPaymentAndRemaining($validated));
    }

    public function isReceived($payment_id)
    {
        return response()->json($this->paymentService->isReceived($payment_id));
    }
}
