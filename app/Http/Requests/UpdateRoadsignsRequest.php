<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoadsignsRequest extends FormRequest
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
            'template_id' => 'required|exists:templates,id',
            'city_id' => 'required|exists:cities,id',
            'region_id' => 'required|exists:regions,id',
            'place' => 'required|string',
            'panels_number' => 'required|integer|min:1|max:100',
            'directions'=>'required|string|max:250'
        ];
    }
}
