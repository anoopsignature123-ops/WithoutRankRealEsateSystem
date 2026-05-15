<?php

namespace App\Services;

use App\Models\CustomerBooking;

class BookingLetterService
{
    public function getBookingDropdown()
    {
        return CustomerBooking::orderByDesc(
            'id'
        )->get();
    }

    public function getBookings(
        $bookingId = null
    ) {
        $query = CustomerBooking::with([

            'primaryDetail',
            'plotSaleDetail.plotDetail.block.project',
            'payment',

        ]);

        if ($bookingId) {

            $query->where(
                'id',
                $bookingId
            );

        }

        return $query->latest()->get();
    }

    public function findBooking($id)
    {
        return CustomerBooking::with([

            'primaryDetail',
            'plotSaleDetail.plotDetail.block.project',
            'payment',

        ])->findOrFail($id);
    }
}
