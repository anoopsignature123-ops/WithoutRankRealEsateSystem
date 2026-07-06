<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerBookingStepFiveRequest extends FormRequest
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

            'account_number' => 'nullable|required_if:payment_mode,cheque,dd|string|max:100',

            'bank_name' => 'nullable|required_if:payment_mode,cheque,dd,neft_rtgs|string|max:150',
            'branch_name' => 'nullable|required_if:payment_mode,cheque,dd,neft_rtgs|string|max:150',

            'cheque_number' => 'nullable|required_if:payment_mode,cheque|string|max:100',
            'dd_number' => 'nullable|required_if:payment_mode,dd|string|max:100',

            'transaction_number' => 'nullable|required_if:payment_mode,neft_rtgs,card|string|max:150',

            'cheque_date' => 'nullable|required_if:payment_mode,cheque,dd,neft_rtgs|date',

            'remark' => 'nullable|string|max:500',
            'plot_sale_detail_id' => 'nullable|required_without:plot_sale_detail_ids|exists:plot_sale_details,id',
            'plot_sale_detail_ids' => 'nullable|array|size:1',
            'plot_sale_detail_ids.*' => 'exists:plot_sale_details,id|distinct',
        ];
    }

    public function messages(): array
    {
        return [
            'plan_type.required' => 'Please select plan type.',
            'payment_mode.required' => 'Please select payment mode.',

            'booking_amount.required' => 'Booking amount is required.',
            'due_amount.required' => 'Due amount is required.',

            'net_payable_amount.required_if' => 'Net payable amount is required for full payment.',

            'emi_months.required_if' => 'EMI months are required for EMI plan.',
            'after_booking_payable_amount.required_if' => 'After booking payable amount is required for EMI plan.',

            'account_number.required_if' => 'Account number is required for cheque and DD payments.',

            'bank_name.required_if' => 'Bank name is required for cheque, DD and NEFT/RTGS payments.',
            'branch_name.required_if' => 'Branch name is required for cheque, DD and NEFT/RTGS payments.',

            'cheque_number.required_if' => 'Cheque number is required for cheque payments.',
            'dd_number.required_if' => 'DD number is required for DD payments.',

            'transaction_number.required_if' => 'Transaction number is required for NEFT/RTGS and card payments.',

            'cheque_date.required_if' => 'Instrument date is required.',

            'plot_sale_detail_id.required' => 'Plot sale detail is required.',
            'plot_sale_detail_id.exists' => 'Selected plot sale detail is invalid.',
            'plot_sale_detail_ids.size' => 'Payment can be saved for one plot booking only.',
        ];
    }
}
