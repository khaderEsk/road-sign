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
    public function __construct(protected PaymentService $paymentService)
    {
        return [
            'role_or_permission:view-payments|create-payments|edit-payments|delete-payments',
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('delete-payments'), only: ['destroy']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('view-payments'), only: ['index', 'show']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('edit-payments'), only: ['update']),
            new Middleware(\Spatie\Permission\Middleware\PermissionMiddleware::using('create-payments'), only: ['store']),
        ];
    }

    public function index(Request $request)
    {
        return response()->json($this->paymentService->getAll($request->all()));
    }

    public function store(PaymentRequest $request)
    {
        $data = $request->validated();
        $data['payment_image'] = $this->uploadImage($request, 'payment_image', 'payments');
        return response()->json($this->paymentService->create($data));
    }

    public function show($id)
    {
        return response()->json($this->paymentService->getById($id));
    }

    public function update(PaymentRequest $request, $id)
    {

        return response()->json($this->paymentService->update($id, $request->validated()));
    }

    public function destroy($id)
    {
        return response()->json(['deleted' => $this->paymentService->delete($id)]);
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
