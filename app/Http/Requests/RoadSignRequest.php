<?php

namespace App\Http\Requests;

use App\Models\Template;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoadSignRequest extends FormRequest
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
            'direction_one' => [
                'required',
                'string',
                Rule::when(function () {
                    $template = Template::find($this->template_id);
                    return $template && $template->faces_number == 2;
                }, ['required', 'string'])
            ],
            'direction_two' => [
                Rule::when(function () {
                    $template = Template::find($this->template_id);
                    return $template && $template->faces_number == 2;
                }, ['required', 'string'])
            ],

        ];
    }
}
