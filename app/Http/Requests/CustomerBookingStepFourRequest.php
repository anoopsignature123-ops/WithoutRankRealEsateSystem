<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerBookingStepFourRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'edit_booking_code' => 'nullable|string|max:255',
            'edit_plot_sale_detail_id' => 'nullable|integer',

            // Required Relations
            'project_id' => 'required|exists:projects,id',

            'block_id' => 'required|exists:blocks,id',

            'plot_detail_ids' => 'required|array|size:1',
            'plot_detail_ids.*' => 'exists:plot_details,id|distinct',
            'plot_details' => 'nullable|array',

            // Plot Fields
            'plot_number' => 'nullable|string|max:500',

            'plot_rate' => 'nullable|numeric|min:0',

            'plot_area' => 'nullable|numeric|min:0',

            'plot_cost' => 'nullable|numeric|min:0',

            'plc_amount' => 'nullable|numeric|min:0',

            // Charges
            'total_development_charge' => 'nullable|numeric|min:0',

            'development_rate' => 'nullable|numeric|min:0',

            'other_charges' => 'nullable|numeric|min:0',

            'coupon_discount' => 'nullable|numeric|min:0',

            'final_payable' => 'required|numeric|min:0',

            'total_plot_cost' => 'required|numeric|min:0',

            // Other
            'booking_date' => 'required|date',

            'remark' => 'nullable|string|max:500',

        ];
    }

    public function messages(): array
    {
        return [

            'project_id.required' => 'Please select property.',
            'project_id.exists' => 'Selected property is invalid.',

            'block_id.required' => 'Please select block.',
            'block_id.exists' => 'Selected block is invalid.',

            'plot_detail_ids.required' => 'Please select plot.',
            'plot_detail_ids.size' => 'Only one plot can be selected for a booking.',
            'plot_detail_ids.*.exists' => 'Selected plot is invalid.',

            'plot_number.required' => 'Plot number is required.',

            'plot_rate.required' => 'Plot rate is required.',

            'plot_area.required' => 'Plot area is required.',

            'plot_cost.required' => 'Plot cost is required.',

            'final_payable.required' => 'Final payable amount is required.',

            'total_plot_cost.required' => 'Total plot cost is required.',

            'booking_date.required' => 'Booking date is required.',

        ];
    }
}
