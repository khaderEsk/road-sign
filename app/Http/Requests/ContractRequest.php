<?php

namespace App\Http\Requests;

use App\ContractStatus;
use App\ContractType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContractRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $this->merge([
            'user_id' => auth()->user()->id,
        ]);
    }
    
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
            'name' => 'required|string',
            'customer_id' => 'required|exists:customers,id',
            'user_id' => 'required|exists:users,id',
            'broker_id' => 'required|exists:brokers,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'type' => ['required',Rule::in(ContractType::cases())],
            'status' => ['required',Rule::in(ContractStatus::cases())],
            // 'done' => 'nullable|boolean',
        ];
    }
}
