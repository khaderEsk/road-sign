<?php

namespace App\Http\Requests;

use App\GeneralTrait;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class PaymentRequest extends FormRequest
{
    use GeneralTrait;
    protected function prepareForValidation()
    {
        $this->merge([
            'customer_id' => auth('customer')->user()->id,
        ]);
    }
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
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
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->returnValidationError('422', $validator));
    }
}
