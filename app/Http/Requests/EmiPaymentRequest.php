<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmiPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_booking_id' => 'required|exists:customer_bookings,id',
            'plot_sale_detail_id' => 'required',
            'booking_amount' => 'required|numeric|min:1',
            'payment_mode' => 'required',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'branch_name' => 'nullable|string|max:255',
            'cheque_number' => 'nullable|string|max:255',
            'cheque_date' => 'nullable|date',
            'dd_number' => 'nullable|string|max:255',
            'transaction_number' => 'nullable|string|max:255',
            'remark' => 'nullable|string',
        ];
    }
}
