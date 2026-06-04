<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DesignationRankRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'designation' => 'required|string|max:255',
            'rank_number' => 'required',
            'commission' => 'required|numeric',
            'target_from' => ['required', 'numeric', 'min:0'],
            'target_to' => ['required', 'numeric', 'gt:target_from'],
        ];
    }

    public function messages(): array
    {
        return [
            'designation.required' => 'Designation is required.',
            'rank_number.required' => 'Rank number is required.',
            'commission.required' => 'Commission is required.',
        ];
    }
}