<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateEmiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'emi_months' => 'required|integer|min:1',
            'emi_amount' => 'required|numeric|min:1',
        ];
    }
}