<?php

namespace App\Services;

use App\Models\CustomerBooking;

class CustomerListService
{
    // Customer list page
    public function getAllCustomers()
    {
        return CustomerBooking::with(['primaryDetail.correspondenceDetail','parentCustomer',])
            ->whereNotNull('customer_id')->latest()->get()->groupBy('customer_id')
            ->map(function ($group) {$customer = $group->first();
                $customer->total_bookings = $group->count();
                return $customer;
            })->values();
    }
    public function getPlotBookingList()
    {
        return CustomerBooking::with([
            'associate',
            'parentCustomer',
            'primaryDetail',
            'plotSaleDetail.project',
            'plotSaleDetail.block',
            'plotSaleDetail.plotDetail',
            'payment',
        ])->whereHas('plotSaleDetail')->whereNotNull('booking_code')->latest()->get();
    }
}
