<?php

namespace App\Services\Associate;

use App\Models\Block;
use App\Models\CustomerBooking;
use App\Models\CustomerPayment;
use App\Models\PlotDetail;
use Illuminate\Support\Collection;

class BookingDetailService
{
    public function getFilteredBookings($request)
    {
        $associateIds = $this->teamAssociateIds();

        $payments = CustomerPayment::with([
            'booking.primaryDetail',
            'booking.associate',
            'plotSaleDetail.project',
            'plotSaleDetail.block',
            'plotSaleDetail.plotDetail',
        ])
            ->whereHas('booking', fn ($q) => $q->whereIn('associate_id', $associateIds))
            ->when($request->project_id, fn ($q) => $q->whereHas('plotSaleDetail.plotDetail.block', fn ($sub) => $sub->where('project_id', $request->project_id)))
            ->when($request->block_id, fn ($q) => $q->whereHas('plotSaleDetail.plotDetail', fn ($sub) => $sub->where('block_id', $request->block_id)))
            ->when($request->plot_id, fn ($q) => $q->where('plot_sale_detail_id', $request->plot_id))
            ->when($request->customer_id, fn ($q) => $q->whereHas('booking', fn ($sub) => $sub->where('customer_code', $request->customer_id)))
            ->when($request->booking_id, fn ($q) => $q->whereHas('booking', fn ($sub) => $sub->where('booking_code', $request->booking_id)))
            ->when($request->from_date, fn ($q) => $q->whereDate('created_at', '>=', $request->from_date))
            ->when($request->to_date, fn ($q) => $q->whereDate('created_at', '<=', $request->to_date))
            ->latest()
            ->get();

        return $this->groupPaymentRecords($payments);
    }

    public function getBlocksByProject($projectId)
    {
        return Block::where('project_id', $projectId)->get();
    }

    public function getPlotsByBlock($blockId)
    {
        return PlotDetail::where('block_id', $blockId)->get();
    }

    public function getBookingDataByPlot($plotId)
    {
        $associateIds = $this->teamAssociateIds();

        $booking = CustomerBooking::whereIn('associate_id', $associateIds)
            ->whereHas('plotSaleDetails', fn ($q) => $q->where('plot_detail_id', $plotId))
            ->latest()
            ->first();

        return $booking ? [
            'customer_id' => $booking->customer_code,
            'booking_id' => $booking->booking_code,
        ] : [];
    }

    private function groupPaymentRecords(Collection $payments): Collection
    {
        return $payments
            ->groupBy(fn ($payment) => $payment->receipt_number ?: 'payment-'.$payment->id)
            ->map(function (Collection $group) {
                $first = $group->sortByDesc('id')->first();
                $booking = $first->booking;
                $plots = $group->pluck('plotSaleDetail')->filter()->unique('id')->values();
                $amount = (float) $group->sum(fn ($payment) => $payment->paid_amount ?? $payment->booking_amount ?? 0);
                $payable = (float) $plots->sum(fn ($plotSale) => $plotSale->total_plot_cost ?? $plotSale->final_payable ?? $plotSale->plot_cost ?? 0);
                $planTypes = $group->pluck('plan_type')->filter()->unique()->values();
                $paymentTypes = $group->pluck('transaction_category')->filter()->unique()->values();
                $statuses = $group->pluck('payment_status')->filter()->unique()->values();

                return (object) [
                    'id' => $first->id,
                    'receipt_number' => $first->receipt_number ?? 'N/A',
                    'booking' => $booking,
                    'payments' => $group->values(),
                    'plots' => $plots,
                    'booking_code' => $plots->pluck('booking_code')->filter()->unique()->implode(', ') ?: ($booking?->booking_code ?? '-'),
                    'customer_name' => $booking?->primaryDetail?->name ?? $booking?->customer_name ?? '-',
                    'customer_code' => $booking?->customer_code ?? '-',
                    'associate_name' => $booking?->associate?->associate_name ?? '-',
                    'project_name' => $plots->pluck('project.name')->filter()->unique()->implode(', ') ?: '-',
                    'block_name' => $plots->pluck('block.block')->filter()->unique()->implode(', ') ?: '-',
                    'plot_numbers' => $plots->pluck('plotDetail.plot_number')->filter()->implode(', ') ?: '-',
                    'plot_count' => $plots->count(),
                    'plan_type' => $planTypes->count() > 1 ? 'mixed' : ($planTypes->first() ?? '-'),
                    'payment_type' => $paymentTypes->count() > 1 ? 'mixed' : ($paymentTypes->first() ?? '-'),
                    'payable_amount' => $payable,
                    'paid_amount' => $amount,
                    'payment_mode' => $first->payment_mode,
                    'payment_status' => $statuses->count() > 1 ? 'mixed' : ($statuses->first() ?? '-'),
                    'created_at' => $first->created_at,
                ];
            })
            ->sortByDesc('created_at')
            ->values();
    }

    public function getTeamBusinessData()
    {
        $associateIds = $this->teamAssociateIds();

        return CustomerBooking::with([
            'associate',
            'primaryDetail',
            'plotSaleDetails.project',
            'plotSaleDetails.block',
            'plotSaleDetails.plotDetail',
        ])
            ->whereIn('associate_id', $associateIds)->latest()
            ->get()
            ->map(function ($booking) {
                $plotSales = $booking->plotSaleDetails;

                return (object) [
                    'booking_code' => $booking->booking_code,
                    'customer_name' => $booking->primaryDetail?->name ?? '-',
                    'agent_name' => $booking->associate?->associate_name ?? '-',
                    'project_name' => $plotSales->pluck('project.name')->filter()->unique()->implode(', ') ?: '-',
                    'plot_no' => $plotSales->pluck('plotDetail.plot_number')->filter()->implode(', ') ?: '-',
                    'amount' => (float) $plotSales->sum(fn ($plotSale) => $plotSale->total_plot_cost ?? $plotSale->final_payable ?? $plotSale->plot_cost ?? 0),
                    'date' => $booking->created_at?->format('d-m-Y'),
                ];
            });
    }

    public function getDueEmiAmountData()
    {
        $associateIds = $this->teamAssociateIds();

        return CustomerBooking::with([
            'associate',
            'primaryDetail',
            'payments',
            'plotSaleDetails.project',
            'plotSaleDetails.block',
            'plotSaleDetails.plotDetail',
            'plotSaleDetails.payments',
        ])
            ->whereIn('associate_id', $associateIds)
            ->latest()
            ->get()
            ->map(function ($booking) {
                $bookingPayment = $booking->payments
                    ->where('plan_type', 'emi_plan')
                    ->where('transaction_category', 'booking_fee')
                    ->sortBy('id')
                    ->first();

                if (! $bookingPayment) {
                    return null;
                }

                $plotSales = $booking->plotSaleDetails
                    ->filter(fn ($plotSale) => $plotSale->payments->contains(fn ($payment) => $payment->plan_type === 'emi_plan'));
                $allPayments = $booking->payments->sortBy('id');
                $emiPayments = $allPayments
                    ->where('transaction_category', 'emi_payment')
                    ->whereIn('payment_status', ['paid', 'cleared']);
                $emiInstallments = $emiPayments
                    ->groupBy(fn ($payment) => $payment->receipt_number ?: 'payment-'.$payment->id)
                    ->map(fn ($group) => $group->sortBy('id')->first())
                    ->values();

                $totalInstallments = (int) ($bookingPayment?->emi_months ?? 0);
                $currentDueAmount = (float) $allPayments->where('plan_type', 'emi_plan')->last()?->due_amount;
                $monthlyEmi = (float) ($bookingPayment?->after_booking_payable_amount ?? 0);
                $paidInstallments = $emiInstallments->count();
                $remainingInstallments = max(0, $totalInstallments - $paidInstallments);

                $emiHistory = [];
                for ($i = 1; $i <= $totalInstallments; $i++) {
                    $paidEmi = $emiInstallments->get($i - 1);

                    $emiHistory[] = [
                        'month' => $i,
                        'emi_amount' => $monthlyEmi,
                        'status' => $paidEmi ? 'Paid' : 'Pending',
                        'paid_date' => $paidEmi ? $paidEmi->created_at->format('d-m-Y') : '-',
                        'receipt_number' => $paidEmi?->receipt_number ?? '-',
                        'payment_mode' => $paidEmi?->payment_mode ?? '-',
                    ];
                }

                return (object) [
                    'booking_code' => $booking->booking_code,
                    'customer_name' => $booking->primaryDetail?->name ?? '-',
                    'associate_name' => $booking->associate?->associate_name ?? '-',
                    'project_name' => $plotSales->pluck('project.name')->filter()->unique()->implode(', ') ?: '-',
                    'block_name' => $plotSales->pluck('block.block')->filter()->unique()->implode(', ') ?: '-',
                    'plot_no' => $plotSales->pluck('plotDetail.plot_number')->filter()->implode(', ') ?: '-',
                    'plot_amount' => round((float) $plotSales->sum(fn ($plotSale) => $plotSale->total_plot_cost ?? $plotSale->final_payable ?? $plotSale->plot_cost ?? 0), 2),
                    'booking_amount' => round((float) ($bookingPayment?->booking_amount ?? 0), 2),
                    'due_amount' => round($currentDueAmount, 2),
                    'emi_amount' => round($monthlyEmi, 2),
                    'total_installments' => $totalInstallments,
                    'paid_installments' => $paidInstallments,
                    'remaining_installments' => $remainingInstallments,
                    'emi_progress' => "{$paidInstallments}/{$totalInstallments}",
                    'progress_percent' => $totalInstallments > 0
                    ? round(($paidInstallments / $totalInstallments) * 100, 2) : 0,
                    'status' => $remainingInstallments > 0 ? 'Pending' : 'Completed',
                    'emi_history' => $emiHistory,
                ];
            })
            ->filter()->values();
    }

    private function teamAssociateIds(): array
    {
        $associate = auth()->guard('associate')->user() ?: auth()->user();

        if (! $associate) {
            return [];
        }

        return collect(method_exists($associate, 'getDownlineIds') ? $associate->getDownlineIds() : [])
            ->push($associate->id)
            ->unique()
            ->values()
            ->all();
    }
}
