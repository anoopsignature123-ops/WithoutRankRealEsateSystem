<?php

namespace App\Services\Associate;

use App\Models\Associate;
use App\Models\CommissionPayout;
use App\Models\CustomerBooking;
use App\Models\CustomerPayment;
use Illuminate\Support\Collection;

class AssociateDashboardService
{
    public function getDashboardStats(int $associateId): array
    {
        $associate = Associate::with('bankDetail')->findOrFail($associateId);

        $bookingIds = CustomerBooking::where('associate_id', $associateId)->pluck('id');

        $basePayments = CustomerPayment::whereIn('customer_booking_id', $bookingIds);

        $confirmedPayments = (clone $basePayments)
            ->whereIn('payment_status', ['paid', 'cleared']);

        $holdPayments = (clone $basePayments)
            ->where('payment_status', 'hold');

        $totalBusiness = (float) (clone $confirmedPayments)->sum('paid_amount');

        $confirmedSales = (float) (clone $confirmedPayments)->sum('paid_amount');

        $pendingSales = (float) $this->latestPaymentPerBooking(
            (clone $basePayments)->get()
        )->sum('due_amount');

        $holdSales = (float) (clone $holdPayments)->sum('paid_amount');

        $recentLedgers = (clone $basePayments)
            ->with([
                'customerBooking.primaryDetail',
                'plotSaleDetail.plotDetail',
                'plotSaleDetail.project',
                'plotSaleDetail.block',
            ])
            ->latest('id')
            ->take(10)
            ->get();

        $chartData = (clone $basePayments)
            ->select('transaction_category', 'booking_status')
            ->selectRaw('SUM(paid_amount) as total_paid')
            ->selectRaw('SUM(booking_amount) as total_booking')
            ->selectRaw('SUM(due_amount) as total_due')
            ->groupBy('transaction_category', 'booking_status')
            ->get();

        return [
            'direct_count' => (int) ($associate->direct_count ?? 0),
            'team_count' => (int) ($associate->downline_count ?? 0),

            'total_business' => $totalBusiness,
            'confirmed_sales' => $confirmedSales,
            'pending_sales' => $pendingSales,
            'hold_sales' => $holdSales,

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

        $categories = [
            'booking_fee',
            'one_time',
            'emi_payment',
        ];

        $result = [];

        foreach ($categories as $category) {
            $filtered = $payments->where('transaction_category', $category);

            $latestPayments = $this->latestPaymentPerBooking($filtered);

            $result[$category] = [
                'pending' => (float) $latestPayments
                    ->whereIn('payment_status', ['pending', 'paid', 'hold'])
                    ->sum('due_amount'),

                'confirmed' => (float) $filtered
                    ->whereIn('payment_status', ['paid', 'cleared'])
                    ->sum('paid_amount'),

                'hold' => (float) $filtered
                    ->where('payment_status', 'hold')
                    ->sum('paid_amount'),

                'total' => (float) $filtered->sum('paid_amount'),
            ];
        }

        return $result;
    }

    public function getBusinessStats(int $associateId): array
    {
        $associate = Associate::findOrFail($associateId);

        $myBookingIds = CustomerBooking::where('associate_id', $associateId)->pluck('id');

        $downlineIds = $associate->getDownlineIds();

        $teamBookingIds = CustomerBooking::whereIn('associate_id', $downlineIds)->pluck('id');

        $selfPayments = CustomerPayment::whereIn('customer_booking_id', $myBookingIds)->get();

        $teamPayments = CustomerPayment::whereIn('customer_booking_id', $teamBookingIds)->get();

        return [
            'self' => [
                'pending' => (float) $this->latestPaymentPerBooking($selfPayments)
                    ->whereIn('payment_status', ['pending', 'paid', 'hold'])
                    ->sum('due_amount'),

                'confirmed' => (float) $selfPayments
                    ->whereIn('payment_status', ['paid', 'cleared'])
                    ->sum('paid_amount'),

                'hold' => (float) $selfPayments
                    ->where('payment_status', 'hold')
                    ->sum('paid_amount'),

                'total' => (float) $selfPayments->sum('paid_amount'),
            ],

            'team' => [
                'pending' => (float) $this->latestPaymentPerBooking($teamPayments)
                    ->whereIn('payment_status', ['pending', 'paid', 'hold'])
                    ->sum('due_amount'),

                'confirmed' => (float) $teamPayments
                    ->whereIn('payment_status', ['paid', 'cleared'])
                    ->sum('paid_amount'),

                'hold' => (float) $teamPayments
                    ->where('payment_status', 'hold')
                    ->sum('paid_amount'),

                'total' => (float) $teamPayments->sum('paid_amount'),
            ],
        ];
    }

    private function latestPaymentPerBooking(Collection $payments): Collection
    {
        return $payments
            ->sortByDesc('id')
            ->unique(fn ($payment) => $payment->customer_booking_id.'-'.$payment->plot_sale_detail_id)
            ->values();
    }

    public function getPayoutStats(int $associateId): array
    {
        $payouts = CommissionPayout::where('associate_id', $associateId)->get();

        $selfCommission = $payouts
            ->where('commission_type', 'self')
            ->sum('commission_amount');

        $teamCommission = $payouts
            ->where('commission_type', 'team')
            ->sum('commission_amount');

        return [
            'self_commission' => $selfCommission,
            'team_commission' => $teamCommission,
            'total_payout' => $selfCommission + $teamCommission,
            'pending_payout' => $payouts
                ->where('status', 'pending')
                ->sum('commission_amount'),
        ];
    }
}
