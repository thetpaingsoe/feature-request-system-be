<?php

namespace App\Http\Requests\Submission;

use Illuminate\Foundation\Http\FormRequest;

class SubmissionUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
            ],
            'company_name' => ['required', 'string', 'max:255'],
            'alternative_company_name' => ['required', 'string', 'max:255'],

            'company_designation_id' => ['required', 'integer', 'exists:company_designations,id'],
            'jurisdiction_of_operation_id' => ['required', 'integer', 'exists:countries,id'],

            'target_jurisdictions' => ['required', 'array'],
            'target_jurisdictions.*' => ['integer', 'exists:countries,id'], // Validate each item in the array

            'number_of_shares' => ['required', 'integer', 'min:0'],
            'are_all_shares_issued' => ['required', 'boolean'], // Must be a boolean (true/false)
            'number_of_issued_shares' => ['required', 'integer', 'min:0'],
            'share_value_id' => ['required', 'integer', 'exists:share_values,id'],

            // For JSON arrays of objects (shareholders, beneficial_owners, directors)
            'shareholders' => ['required', 'array'],
            'shareholders.*.name' => ['required', 'string', 'max:255'],
            'shareholders.*.email' => ['required', 'string', 'email', 'max:255'],
            'shareholders.*.percentage' => ['required', 'integer'],

            'beneficial_owners' => ['required', 'array'],
            'beneficial_owners.*.name' => ['required', 'string', 'max:255'],
            'beneficial_owners.*.email' => ['required', 'string', 'email', 'max:255'],

            'directors' => ['required', 'array'],
            'directors.*.name' => ['required', 'string', 'max:255'],
            'directors.*.email' => ['required', 'string', 'email', 'max:255'],

        ];
    }
}
