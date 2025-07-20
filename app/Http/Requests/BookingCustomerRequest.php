<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\BookingType;
use App\DiscountType;
use App\GeneralTrait;
use App\ProductType;
use Illuminate\Validation\Rule;

class BookingCustomerRequest extends FormRequest
{
    use GeneralTrait;
    protected function prepareForValidation()
    {
        $this->merge([
            'user_id' => 2,
            'customer_id' => auth('customer')->user()->id,
            'type' => 1
        ]);
    }
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
            'user_id' => 'required|exists:users,id',
            'customer_id' => 'required|exists:customers,id',
            'type' => ['required', Rule::in(BookingType::cases())],
            'start_date' => 'required|date|after:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'roadsigns' => 'required|array|min:1',
            'roadsigns.*.road_sign_id' => ['required', 'exists:road_signs,id'],
            'roadsigns.*.booking_faces' => ['required', 'integer'],
            'roadsigns.*.number_of_reserved_panels' => ['required', 'integer'],
            'roadsigns.*.start_date' => 'required|date|after:today',
            'roadsigns.*.end_date' => 'required|date|after_or_equal:roadsigns.*.start_date',
            'notes' => ['nullable'],
            'product_type' => ['required', Rule::in(ProductType::cases())],
            'discount_type' => ['nullable', Rule::in(DiscountType::cases())],
            'value' => [
                'nullable',
                'required_with:discount_type',
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
            ],

        ];
    }
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->returnValidationError('422', $validator));
    }
}
