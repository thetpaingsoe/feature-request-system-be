<?php

namespace App\Http\Requests\FeatureRequest;

use Illuminate\Foundation\Http\FormRequest;

class FeatureRequestStoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
            ],
            'description' => [
                'required', 'min:10',
            ],
        ];
    }
}
