<?php

namespace App\Services\Associate;

use App\Models\Associate;
use App\Models\CustomerBooking;
use App\Models\CustomerPayment;

class AssociateDashboardService
{
    public function getDashboardStats(int $associateId): array
    {
        $associate = Associate::with('bankDetail')->findOrFail($associateId);
        $bookingIds = CustomerBooking::where('associate_id', $associateId)->pluck('id');
        $payments = CustomerPayment::whereIn('customer_booking_id', $bookingIds);
        // Service mein ye change karke dekhein (Force cast to float/int)
        $stats = [
            'total_business' => (float) $payments->sum('booking_amount'),
            'confirmed_sales' => (float) (clone $payments)->where('payment_status', 'booked')->sum('booking_amount'),
            'pending_sales' => (float) (clone $payments)->where('payment_status', 'booked')->sum('due_amount'),
        ];
        $recentLedgers = $payments->with(['customerBooking.plotSaleDetail.plotDetail'])->latest()->take(10)->get();

        $chartData = $payments->select('transaction_category', 'payment_status')
            ->selectRaw('SUM(booking_amount) as total')
            ->groupBy('transaction_category', 'payment_status')
            ->get();

        return [
            'direct_count' => $associate->direct_count,
            'team_count' => $associate->downline_count,
            'total_business' => $stats['total_business'],
            'confirmed_sales' => $stats['confirmed_sales'],
            'pending_sales' => $stats['pending_sales'],
            'recent_ledgers' => $recentLedgers,
            'chart_data' => $chartData,
        ];
    }

    public function getMonthlyBusinessData(int $associateId): array
    {
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();
        $bookingIds = CustomerBooking::where('associate_id', $associateId)->pluck('id');
        $payments = CustomerPayment::whereIn('customer_booking_id', $bookingIds)
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $categories = ['booking_fee', 'one_time', 'emi_payment'];
        $result = [];
        foreach ($categories as $cat) {
            $filtered = $payments->where('transaction_category', $cat);
            $result[$cat] = [
                'pending' => $filtered->where('payment_status', 'pending')->sum('booking_amount'),
                'confirmed' => $filtered->where('payment_status', 'booked')->sum('booking_amount'),
            ];
        }

        return $result;
    }

    public function getBusinessStats($associateId)
    {
        $associate = Associate::findOrFail($associateId);
        $myBookingIds = CustomerBooking::where('associate_id', $associateId)->pluck('id');
        $teamBookingIds = CustomerBooking::whereIn('associate_id', $associate->getDownlineIds())->pluck('id');

        return [
            'self' => [
                'pending' => CustomerPayment::whereIn('customer_booking_id', $myBookingIds)->where('payment_status', 'hold')->sum('booking_amount'),
                'confirmed' => CustomerPayment::whereIn('customer_booking_id', $myBookingIds)->where('payment_status', 'booked')->sum('booking_amount'),
            ],
            'team' => [
                'pending' => CustomerPayment::whereIn('customer_booking_id', $teamBookingIds)->where('payment_status', 'hold')->sum('booking_amount'),
                'confirmed' => CustomerPayment::whereIn('customer_booking_id', $teamBookingIds)->where('payment_status', 'booked')->sum('booking_amount'),
            ],
        ];
    }
}
