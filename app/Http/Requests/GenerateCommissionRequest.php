<?php

namespace App\Http\Requests;

use App\Services\CommissionPayoutService;
use Illuminate\Foundation\Http\FormRequest;

class GenerateCommissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $fromDate = app(CommissionPayoutService::class)->getNextGlobalFromDate();

        return [
            'to_date' => [
                'required',
                'date',
                'before_or_equal:today',
                'after_or_equal:' . $fromDate,
            ],
        ];
    }

    public function messages(): array
    {
        $fromDate = app(CommissionPayoutService::class)->getNextGlobalFromDate();

        return [
            'to_date.required' => 'Please select To Date.',
            'to_date.before_or_equal' => 'To Date cannot be future date.',
            'to_date.after_or_equal' => 'Commission already generated before this date. Please select date from ' . date('d M Y', strtotime($fromDate)) . ' or after.',
        ];
    }
}