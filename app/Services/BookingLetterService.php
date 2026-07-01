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

        return $query->latest('id')
            ->get()
            ->groupBy(function ($plotSale) {
                return $plotSale->customer_booking_id.'|'.($plotSale->booking_code ?: $plotSale->id);
            })
            ->map(function ($group) {
                $representative = $group->first();
                $representative->setRelation('letterPlotSales', $group->values());

                return $representative;
            })
            ->values();
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

            $plotSales = $plotSale->booking_code
                ? PlotSaleDetail::with([
                    'project',
                    'block',
                    'plotDetail',
                    'payments',
                ])
                    ->where('customer_booking_id', $booking->id)
                    ->where('booking_code', $plotSale->booking_code)
                    ->orderBy('id')
                    ->get()
                : collect([$plotSale]);

            $booking->setRelation('plotSaleDetail', $plotSale);
            $booking->setRelation('plotSaleDetails', $plotSales);
            $payments = $plotSales->flatMap(function ($sale) {
                return $sale->payments;
            })->values();
            $booking->setRelation('payments', $payments);
            $booking->setRelation('payment', $payments->first());
        }

        return $booking;
    }
}
