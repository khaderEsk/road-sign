<?php

// app/Http/Requests/TemplateRequest.php

namespace App\Http\Requests;

use App\ProductType;
use App\TemplateType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TemplateRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'model' => 'required|string',
            'type' => 'required|string',
            'size' => 'required|string',
            'advertising_space' => 'required|numeric',
            'printing_space' => 'required|numeric',
            'user_id' => 'required|exists:users,id',
            'faces_number' => 'required|min:1|max:2|integer',
            'products' => 'required|array|max:3|min:3',
            'appearance' => ['required', Rule::in(TemplateType::cases())],
            'products.*.type' => ['required', Rule::in(ProductType::cases())],
            'products.*.price' => ['required', 'numeric', 'max:999999', 'min:1']
        ];
    }
}
