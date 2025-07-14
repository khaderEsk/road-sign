<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class UpdateUserRequest extends FormRequest
{

    public function rules(Request $request): array
    {

        return [
            'username' => 'required|string|unique:users,username,' . $this->route('user'),
            'email' => 'required|string|unique:users,email,' . $this->route('user'),
            'full_name' => 'required|string',
            'phone_number' => 'required|string|unique:users,phone_number,' . $this->route('user'),
            'address' => 'nullable|string',
            'password' => 'required|string|min:6',
            'roles' => 'array|exists:roles,name',
            'permissions' => 'array|exists:permissions,name',
        ];
    }
}
