<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlotRegistryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'project_id' => [
                'required',
                'exists:projects,id',
            ],

            'block_id' => [
                'required',
                'exists:blocks,id',
            ],

            'plot_detail_id' => [
                'required',
                'exists:plot_details,id',
            ],

            'customer_booking_id' => [
                'required',
                'exists:customer_bookings,id',
            ],

            'gata_number' => [
                'required',
                'string',
                'max:100',
            ],

            'seller_name' => [
                'required',
                'string',
                'max:255',
            ],

            'register_no' => [
                'required',
                'string',
                'max:100',
            ],

            'register_date' => [
                'required',
                'date',
            ],

        ];
    }

    public function messages(): array
    {
        return [

            'project_id.required' => 'Please select site.',
            'block_id.required' => 'Please select block.',
            'plot_detail_id.required' => 'Please select plot.',
            'customer_booking_id.required' => 'Booking not found for selected plot.',

            'gata_number.required' => 'Gata number is required.',
            'seller_name.required' => 'Seller name is required.',
            'register_no.required' => 'Registry number is required.',
            'register_date.required' => 'Registry date is required.',

        ];
    }
}
