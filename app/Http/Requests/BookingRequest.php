<?php

namespace App\Http\Requests;

use App\BookingType;
use App\DiscountType;
use App\ProductType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class BookingRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $this->merge([
            'user_id' => auth()->user()->id,
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

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $roadsigns = $this->input('roadsigns', []);
            $bookingStart = $this->input('start_date');
            $bookingEnd = $this->input('end_date');

            $roadSignIds = collect($roadsigns)->pluck('road_sign_id');

            // if ($roadSignIds->duplicates()->isNotEmpty()) {
            //     $validator->errors()->add('roadsigns', 'لا يمكن تكرار نفس لوحة الطريق أكثر من مرة.');
            // }

            foreach ($roadsigns as $index => $roadsign) {
                $rsStart = $roadsign['start_date'] ?? null;
                $rsEnd = $roadsign['end_date'] ?? null;

                if ($rsStart && $bookingStart && $rsStart < $bookingStart) {
                    $validator->errors()->add("roadsigns.$index.start_date", 'تاريخ بداية اللوحة يجب أن يكون ضمن فترة الحجز الرئيسية.');
                }

                if ($rsEnd && $bookingEnd && $rsEnd > $bookingEnd) {
                    $validator->errors()->add("roadsigns.$index.end_date", 'تاريخ نهاية اللوحة يجب أن يكون ضمن فترة الحجز الرئيسية.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'start_date.after' => 'حقل تاريخ البداية يجب أن يكون بعد تاريخ اليوم.',
            'roadsigns.*.start_date.after' => 'تاريخ بداية اللوحة يجب أن يكون بعد تاريخ اليوم.',
            'roadsigns.*.end_date.after_or_equal' => 'تاريخ نهاية اللوحة يجب أن يكون بعد أو يساوي تاريخ بدايتها.',
        ];
    }
}
