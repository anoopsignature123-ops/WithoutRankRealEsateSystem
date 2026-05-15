<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssociateAdvanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'associate_id' => ['required', 'exists:associates,id'],
            'advance_amount' => ['required', 'numeric', 'min:1'],
            'advance_date' => ['required', 'date'],
            'remarks' => ['nullable', 'string'],
        ];
    }
}