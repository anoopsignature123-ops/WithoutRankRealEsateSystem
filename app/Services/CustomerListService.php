<?php

namespace App\Services;

use App\Models\CustomerBooking;

class CustomerListService
{
    public function getAllCustomers()
    {
        return CustomerBooking::with([
            'primaryDetail.correspondenceDetail',
            'parentCustomer',
            'plotSaleDetails' => function ($query) {
                $query->whereNotNull('booking_code')
                    ->where('status', 'active');
            },
            'plotSaleDetails.project',
            'plotSaleDetails.block',
            'plotSaleDetails.plotDetail',
        ])
            ->whereHas('plotSaleDetails', function ($query) {
                $query->whereNotNull('booking_code')
                    ->where('status', 'active');
            })
            ->latest()
            ->get()
            ->groupBy('customer_code')
            ->map(function ($group) {
                $customer = $group->first();

                $plots = $group->flatMap(function ($booking) {
                    return $booking->plotSaleDetails;
                });

                $customer->booked_plots = $plots;
                $customer->total_bookings = $plots->count();

                return $customer;
            })
            ->values();
    }

    public function getPlotBookingList()
    {
        return CustomerBooking::with([
            'associate',
            'parentCustomer',
            'primaryDetail',
            'plotSaleDetail' => function ($query) {
                $query->whereNotNull('booking_code')
                    ->where('status', 'active');
            },
            'plotSaleDetail.project',
            'plotSaleDetail.block',
            'plotSaleDetail.plotDetail',
            'payment',
        ])
            ->whereHas('plotSaleDetail', function ($query) {
                $query->whereNotNull('booking_code')
                    ->where('status', 'active');
            })
            ->latest()
            ->get();
    }
}