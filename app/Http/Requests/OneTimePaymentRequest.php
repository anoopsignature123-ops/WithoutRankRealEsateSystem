<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OneTimePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            // Main required fields
            'customer_booking_id' => 'required|exists:customer_bookings,id',

            'plot_sale_detail_id' => 'required|exists:plot_sale_details,id',

            'booking_amount' => 'required|numeric|min:1',

            'payment_mode' => 'required|in:cash,cheque,dd,neft_rtgs,card',

            // Optional receipt
            'manual_receipt_number' => 'nullable|string|max:100',

            // Bank Details
            'bank_name' => 'nullable|string|max:100',

            'account_number' => 'nullable|string|max:100',

            'branch_name' => 'nullable|string|max:100',

            // Cheque Details
            'cheque_number' => 'nullable|string|max:100',

            'cheque_date' => 'nullable|date',

            // DD Details
            'dd_number' => 'nullable|string|max:100',

            // Online/Card
            'transaction_number' => 'nullable|string|max:100',

            // Remarks
            'remark' => 'nullable|string|max:500',

        ];
    }

    public function messages(): array
    {
        return [

            'customer_booking_id.required' => 'Please select booking.',

            'plot_sale_detail_id.required' => 'Plot details missing.',

            'booking_amount.required' => 'Please enter payment amount.',

            'booking_amount.numeric' => 'Amount must be numeric.',

            'payment_mode.required' => 'Please select payment mode.',

        ];
    }
}
