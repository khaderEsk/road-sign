<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'username' => 'required|string|unique:users,username',
            'email' => 'required|string|unique:users,email',
            'full_name' => 'required|string',
            'phone_number' => 'required|string|unique:users,phone_number',
            'address' => 'nullable|string',
            'password' => 'required|string|min:6',
            'roles' => 'array|exists:roles,name',
            'permissions' => 'array|exists:permissions,name',
        ];
    }
}
