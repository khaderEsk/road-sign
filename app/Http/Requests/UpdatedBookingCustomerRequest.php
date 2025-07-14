<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use App\BookingStatus;
use App\BookingType;
use App\DiscountType;
use App\GeneralTrait;
use App\Models\Booking;
use App\Models\User;
use App\ProductType;
use Illuminate\Contracts\Validation\Validator;

class UpdatedBookingCustomerRequest extends FormRequest
{
    use GeneralTrait;
    protected function prepareForValidation()
    {
        $firstUserId = User::orderBy('id')->first()->id;
        $this->merge([
            'user_id' => $firstUserId,
            'customer_id' => auth('customer')->user()->id,
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
            'type' => [
                'required',
                Rule::in(BookingType::cases()),
                function ($attribute, $value, $fail) {
                    $booking = Booking::find($this->route('booking'));

                    if ($booking && $booking->type === BookingType::PERMANENT->value && in_array($booking->status, [BookingStatus::INSTALLED->value, BookingStatus::COMPLETED->value])) {
                        $fail("غير مسموح بتعديل الحجز الدائم إلا إذا كان حالته 'قيد المعالجة'");
                    }

                    if ($booking && in_array($booking->status, [BookingStatus::INSTALLED->value, BookingStatus::COMPLETED->value])) {
                        $fail("لا يمكن تعديل حجز تم تركيبه أو انتهاؤه");
                    }
                }
            ],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'roadsigns' => 'required|array|min:1',
            'roadsigns.*.road_sign_id' => ['required', 'exists:road_signs,id'],
            'roadsigns.*.booking_faces' => ['required', 'integer'],
            'roadsigns.*.number_of_reserved_panels' => ['required', 'integer'],
            'roadsigns.*.start_date' => ['required', 'date'],
            'roadsigns.*.end_date' => ['required', 'date', 'after_or_equal:roadsigns.*.start_date'],
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
            'start_date.required' => 'تاريخ بداية الحجز مطلوب.',
            'end_date.required' => 'تاريخ نهاية الحجز مطلوب.',
            'end_date.after_or_equal' => 'تاريخ نهاية الحجز يجب أن يكون بعد أو يساوي تاريخ البداية.',
            'roadsigns.required' => 'يجب اختيار على الأقل لوحة طريق واحدة.',
            'roadsigns.*.start_date.required' => 'تاريخ بداية اللوحة مطلوب.',
            'roadsigns.*.end_date.required' => 'تاريخ نهاية اللوحة مطلوب.',
            'roadsigns.*.end_date.after_or_equal' => 'تاريخ نهاية اللوحة يجب أن يكون بعد أو يساوي تاريخ بدايتها.',
        ];
    }
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->returnValidationError('422', $validator));
    }
}
