<?php

namespace App\Http\Requests;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerRequest extends FormRequest
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
            'full_name' => 'required|string',
            'company_name' => ['required', 'string'],
            'commercial_registration_number' => 'nullable|string',
            'phone_number' => 'required|string|size:10',
            'address' => 'required|string|max:250',
            'password' => 'required|string|min:6|regex:/[a-zA-Z]/',
            'alt_phone_number' => ['array', 'nullable'],
            'is_tracking' => 'required|boolean',
            'customer.full_name' => 'required_if:is_tracking,1|max:250',
            'customer.phone_number' => 'required_if:is_tracking,1|max:12',
            'customer.address' => 'required_if:is_tracking,1',
        ];
    }
}
