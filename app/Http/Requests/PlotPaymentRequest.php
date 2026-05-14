<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlotPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plan_type' => 'required|in:full_payment,emi_plan',
            'payment_mode' => 'required|in:cash,cheque,dd,neft_rtgs,card',
            'booking_amount' => 'required|numeric|min:0',
            'due_amount' => 'required|numeric|min:0',
            'net_payable_amount' => 'nullable|required_if:plan_type,full_payment|numeric|min:0',
            'emi_months' => 'nullable|required_if:plan_type,emi_plan|integer|min:1',
            'after_booking_payable_amount' => 'nullable|required_if:plan_type,emi_plan|numeric|min:0',
            'account_number' => 'nullable|required_if:payment_mode,cheque,dd,neft_rtgs,card',
            'bank_name' => 'nullable|required_if:payment_mode,cheque,dd,neft_rtgs,card',
            'branch_name' => 'nullable|required_if:payment_mode,cheque,dd,neft_rtgs,card',
            'transaction_number' => 'nullable|string',
            'receipt_number' => 'nullable|string',
            'cheque_number' => 'nullable|required_if:payment_mode,cheque|string',
            'dd_number' => 'nullable|required_if:payment_mode,dd|string',
            'cheque_date' => 'nullable|required_if:payment_mode,cheque,dd,neft_rtgs|date',
            'plot_sale_detail_id' => 'nullable|exists:plot_sale_details,id',
        ];
    }

    public function messages(): array
    {
        return [
            'plan_type.required' => 'Please select plan type.',
            'payment_mode.required' => 'Please select payment mode.',
            'booking_amount.required' => 'Booking amount is required.',
            'net_payable_amount.required_if' => 'Net payable amount is required.',
            'after_booking_payable_amount.required_if' => 'After booking payable amount is required.',
            'account_number.required_if' => 'Account number is required.',
            'bank_name.required_if' => 'Bank name is required.',
            'branch_name.required_if' => 'Branch name is required.',
            'cheque_number.required_if' => 'Cheque number is required for cheque payments.',
            'dd_number.required_if' => 'DD number is required for DD payments.',
            'emi_months.required_if' => 'EMI months are required for EMI plan.',
            'cheque_date.required_if' => 'Instrument date is required.',
        ];
    }
}
