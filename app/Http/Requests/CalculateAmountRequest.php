<?php

namespace App\Http\Requests;

use App\ProductType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CalculateAmountRequest extends FormRequest
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
            'roadsigns' => ['array', 'required', 'min:1'],
            'roadsigns.*.road_sign_id' => ['required', 'exists:road_signs,id'],
            'roadsigns.*.booking_faces' => ['required', 'numeric'],
            'roadsigns.*.start_date' => 'required|date|after:today',
            'roadsigns.*.end_date' => 'required|date|after_or_equal:start_date',
            'roadsigns.*.number_of_reserved_panels' => ['required', 'integer'],
            'product_type' => ['required', Rule::in(ProductType::cases())],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ];
    }
}
