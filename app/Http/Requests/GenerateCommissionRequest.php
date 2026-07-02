<?php

namespace App\Http\Requests;

use App\Services\CommissionPayoutService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class GenerateCommissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'commission_date' => ['required', 'date', 'before_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'commission_date.required' => 'Please select commission date.',
            'commission_date.date' => 'Please select a valid commission date.',
            'commission_date.before_or_equal' => 'Please select a commission date that is not in the future.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if (!$this->filled('commission_date')) {
                return;
            }

            try {
                $service = app(CommissionPayoutService::class);

                $service->resolveCommissionDatePeriod($this->commission_date);
            } catch (\Throwable $e) {
                $validator->errors()->add(
                    'commission_date',
                    $e->getMessage() ?: 'Please select a valid commission date.'
                );
            }
        });
    }
}