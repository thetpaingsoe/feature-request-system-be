<?php

namespace App\Http\Requests\SubmissionLog;

use Illuminate\Foundation\Http\FormRequest;

class SubmissionLogReplyRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'note' => [],
            'status' => [],
            'action' => [],
        ];
    }
}
