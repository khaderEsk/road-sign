<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompanyRequest extends FormRequest
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
            'name' => ['required', 'max:250'],
            'commercial_registration_number' => ['required', 'numeric'],
            'address' => ['required', 'max:250'],
            'description' => ['required'],
            'about_us' => ['required'],
            'contract_note' => ['required'],
        ];
    }
}
