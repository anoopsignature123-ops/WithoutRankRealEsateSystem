<?php

namespace App\Services\Associate;

use App\Models\Associate;
use App\Models\CommissionPayout;
use App\Models\CustomerBooking;
use App\Models\CustomerPayment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class AssociateDashboardService
{
    public function getDashboardStats(int $associateId): array
    {
        $associate = Associate::with('bankDetail')->findOrFail($associateId);

        $bookingIds = CustomerBooking::where('associate_id', $associateId)->pluck('id');

        $basePayments = $this->bookedActivePaymentsQuery($bookingIds);

        $payments = (clone $basePayments)->get();

        $totalBusiness = (float) $payments->sum('paid_amount');

        $recentLedgers = (clone $basePayments)
            ->with([
                'customerBooking.primaryDetail',
                'plotSaleDetail.plotDetail',
                'plotSaleDetail.project',
                'plotSaleDetail.block',
            ])->latest('id')->take(10)->get();

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

            'left_direct_count' => $associate->children()
                ->where('direction', 'left')->count(),

            'right_direct_count' => $associate->children()
                ->where('direction', 'right')->count(),

            'left_team_count' => Associate::whereIn('id', $associate->getDownlineIds())
                ->where('direction', 'left')->count(),

            'right_team_count' => Associate::whereIn('id', $associate->getDownlineIds())
                ->where('direction', 'right')->count(),

            'total_business' => $totalBusiness,
            'confirmed_sales' => $totalBusiness,
            'pending_sales' => 0,
            'hold_sales' => 0,

            'recent_ledgers' => $recentLedgers,
            'chart_data' => $chartData,
        ];
    }

    public function getMonthlyBusinessData(int $associateId): array
    {
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();

        $bookingIds = CustomerBooking::where('associate_id', $associateId)->pluck('id');

        $payments = $this->bookedActivePaymentsQuery($bookingIds)
            ->whereBetween('created_at', [$start, $end])->get();
        $categories = ['booking_fee', 'one_time', 'emi_payment'];
        $result = [];
        foreach ($categories as $category) {
            $filtered = $payments->where('transaction_category', $category);
            $confirmed = (float) $filtered->sum('paid_amount');
            $result[$category] = ['pending' => 0, 'confirmed' => $confirmed, 'hold' => 0, 'total' => $confirmed];
        }
        return $result;
    }

    public function getBusinessStats(int $associateId): array
    {
        $associate = Associate::findOrFail($associateId);
        $myBookingIds = CustomerBooking::where('associate_id', $associateId)->pluck('id');
        $downlineIds = $associate->getDownlineIds();
        $teamBookingIds = CustomerBooking::whereIn('associate_id', $downlineIds)->pluck('id');
        $selfPayments = $this->bookedActivePaymentsQuery($myBookingIds)->get();
        $teamPayments = $this->bookedActivePaymentsQuery($teamBookingIds)->get();
        $selfConfirmed = (float) $selfPayments->sum('paid_amount');
        $teamConfirmed = (float) $teamPayments->sum('paid_amount');

        return [
            'self' => ['pending' => 0, 'confirmed' => $selfConfirmed, 'hold' => 0, 'total' => $selfConfirmed],
            'team' => ['pending' => 0, 'confirmed' => $teamConfirmed, 'hold' => 0, 'total' => $teamConfirmed],
        ];
    }

    private function bookedActivePaymentsQuery($bookingIds): Builder
    {
        return CustomerPayment::whereIn('customer_booking_id', $bookingIds)
            ->where('booking_status', 'booked')
            ->whereIn('payment_status', ['paid', 'cleared'])
            ->whereHas('plotSaleDetail', function ($query) {
                $query->where('status', 'active');
            });
    }

    private function latestPaymentPerBooking(Collection $payments): Collection
    {
        return $payments->sortByDesc('id')
            ->unique(fn($payment) => $payment->customer_booking_id . '-' . $payment->plot_sale_detail_id)->values();
    }

    public function getPayoutStats(int $associateId): array
    {
        $payouts = CommissionPayout::where('associate_id', $associateId)->get();
        $selfCommission = $payouts
            ->where('commission_type', 'self')->sum('commission_amount');
        $teamCommission = $payouts
            ->where('commission_type', 'team')->sum('commission_amount');
        return [
            'self_commission' => $selfCommission,
            'team_commission' => $teamCommission,
            'total_payout' => $selfCommission + $teamCommission,
            'pending_payout' => $payouts->where('status', 'pending')->sum('commission_amount'),
        ];
    }
}