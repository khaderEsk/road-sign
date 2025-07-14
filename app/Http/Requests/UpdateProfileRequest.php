<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
            'full_name' => 'sometimes|string|between:2,100',
            'username' => 'required|string|unique:users,username,'.Auth()->user()->id,
            'phone_number' => 'required|string|unique:users,phone_number,'.Auth()->user()->id,
            'address' => 'sometimes|string',
            'password' => 'required|string|min:6|regex:/[a-zA-Z]/',
        ];
    }
}
