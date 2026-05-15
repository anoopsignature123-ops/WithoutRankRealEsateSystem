<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MultipleChequeClearanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'payment_ids' => [
                'required',
            ],

            'cheque_status' => [
                'required',
                'in:cleared,cancelled,bounced,pending',
            ],

            'cheque_reason' => [
                'nullable',
                'string',
            ],

        ];
    }
}
