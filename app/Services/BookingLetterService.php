<?php

namespace App\Services;

use App\Models\CustomerBooking;
use App\Models\PlotSaleDetail;

class BookingLetterService
{
    public function getBookingDropdown()
    {
        return CustomerBooking::with('primaryDetail')
            ->whereNotNull('booking_code')
            ->latest('id')
            ->get();
    }

    public function getBookings($bookingId = null)
    {
        $query = PlotSaleDetail::with([
            'customerBooking.primaryDetail.correspondenceDetail',
            'project',
            'block',
            'plotDetail',
            'payments',
        ])
            ->whereNotNull('booking_code')
            ->whereHas('customerBooking', function ($query) {
                $query->whereNotNull('booking_code');
            });

        if ($bookingId) {
            $query->where('customer_booking_id', $bookingId);
        }

        return $query->latest('id')->get();
    }

    public function findBooking($id, $plotSaleDetailId = null)
    {
        $booking = CustomerBooking::with([
            'primaryDetail',
            'secondaryDetail',
            'nomineeDetail',
            'plotSaleDetail.project',
            'plotSaleDetail.block',
            'plotSaleDetail.plotDetail',
            'payment',
            'payments',
            'associate',
        ])->findOrFail($id);

        if ($plotSaleDetailId) {
            $plotSale = PlotSaleDetail::with([
                'project',
                'block',
                'plotDetail',
                'payments',
            ])
                ->where('customer_booking_id', $booking->id)
                ->findOrFail($plotSaleDetailId);

            $booking->setRelation('plotSaleDetail', $plotSale);
            $booking->setRelation('payments', $plotSale->payments);
            $booking->setRelation('payment', $plotSale->payments->first());
        }

        return $booking;
    }
}
