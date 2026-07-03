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
                    ->where('status', 'active')
                    ->whereHas('payments', function ($paymentQuery) {
                        $paymentQuery->whereIn('booking_status', ['booked', 'hold']);
                    });
            },
            'plotSaleDetails.project',
            'plotSaleDetails.block',
            'plotSaleDetails.plotDetail',
            'plotSaleDetails.payments' => function ($query) {
                $query->select('id', 'plot_sale_detail_id', 'booking_status', 'payment_status')
                    ->latest('id');
            },
        ])
            ->whereHas('plotSaleDetails', function ($query) {
                $query->whereNotNull('booking_code')
                    ->where('status', 'active')
                    ->whereHas('payments', function ($paymentQuery) {
                        $paymentQuery->whereIn('booking_status', ['booked', 'hold']);
                    });
            })
            ->latest()
            ->get()
            ->groupBy('customer_code')
            ->map(function ($group) {
                $customer = $group->first();

                $plots = $group->flatMap(function ($booking) {
                    return $booking->plotSaleDetails;
                })->map(function ($plot) {
                    $bookingStatuses = $plot->payments
                        ->pluck('booking_status')
                        ->filter()
                        ->map(fn ($status) => strtolower($status))
                        ->unique();

                    $plot->admin_booking_status = $bookingStatuses->contains('booked')
                        ? 'booked'
                        : ($bookingStatuses->contains('hold') ? 'hold' : ($plot->plotDetail?->status ?? 'N/A'));

                    return $plot;
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
