<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class PaymentBrokerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_id' => 'required',
            'paid' => [
                'required',
                'numeric',
                'min:1',
                function ($attribute, $value, $fail) {
                    $remaining = DB::table('customers')
                        ->value('remaining');
                    if ($value > $remaining) {
                        $fail(". ($remaining) لا يمكن أن يتجاوز المبلغ المدفوع الرصيد المتبقي");
                    }
                },
            ],
            'payment_image' => 'required|image',
        ];
    }
}
