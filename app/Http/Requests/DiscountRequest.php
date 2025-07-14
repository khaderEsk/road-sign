<?php

namespace App\Http\Requests;

use App\DiscountType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DiscountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'user_id' => auth()->user()->id,
        ]);
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'discount_type' => ['nullable', Rule::in(DiscountType::cases())],
            'value' => [
                'nullable',
                'required',
                'numeric',
                function ($attribute, $value, $fail) {

                    $type = $this->discount_type;
                    if ($type == DiscountType::PERCENTAGE->value && ($value < 0 || $value > 100)) {
                        $fail("يجب أن تكون النسبة المئوية بين 0 و 100.");
                    }
                    if ($type == DiscountType::AMOUNT->value && $value < 0) {
                        $fail("يجب أن يكون المبلغ على الأقل 0.");
                    }
                }
            ]
        ];
    }
}
