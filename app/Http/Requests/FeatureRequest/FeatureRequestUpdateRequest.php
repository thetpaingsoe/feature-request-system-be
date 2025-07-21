<?php

namespace App\Http\Requests\FeatureRequest;

use Illuminate\Foundation\Http\FormRequest;

class FeatureRequestUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => [],
            'note' => [],
        ];
    }
}
