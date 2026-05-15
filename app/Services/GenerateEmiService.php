<?php

namespace App\Services;

use App\Models\CustomerBooking;
use App\Models\CustomerPayment;

class GenerateEmiService
{
    public function getCustomers()
    {
        return CustomerBooking::with('primaryDetail')->get();
    }

    public function getList($customerId = null)
    {
        $query = CustomerBooking::with([
            'primaryDetail',
            'payment',
            'plotSaleDetail.plotDetail',
            'associate',
        ]);

        if ($customerId) {
            $query->where('id', $customerId);
        }

        return $query->get();
    }

    public function generate($bookingId, array $data)
    {
        $booking = CustomerBooking::findOrFail($bookingId);

        CustomerPayment::updateOrCreate(
            ['customer_booking_id' => $bookingId],
            [
                'plot_sale_detail_id' => $booking->plotSaleDetail?->id,
                'plan_type' => 'emi_plan',
                'emi_months' => $data['emi_months'],
                'after_booking_payable_amount' => $data['emi_amount'],
                'payment_status' => 'emi',
            ]
        );

        return true;
    }
}