<?php

namespace App\Services\Associate;

use App\Models\Associate;
use App\Models\Block;
use App\Models\CustomerBooking;
use App\Models\CustomerPayment;
use App\Models\PlotDetail;

class BookingDetailService
{
    public function getFilteredBookings($request)
    {
        $associateId = auth()->user()->id;
        $downlineIds = Associate::where('sponsor_id', $associateId)
            ->pluck('associate_id')
            ->push($associateId) // Khud ko include karna
            ->toArray();

        return CustomerPayment::with([
            'booking.primaryDetail',
            'booking.associate',
            'plotSaleDetail.plotDetail.block.project',
        ])
            ->whereHas('booking', fn ($q) => $q->whereIn('associate_id', $downlineIds))
            ->when($request->project_id, fn ($q) => $q->whereHas('plotSaleDetail.plotDetail.block', fn ($sub) => $sub->where('project_id', $request->project_id)))
            ->when($request->block_id, fn ($q) => $q->whereHas('plotSaleDetail.plotDetail', fn ($sub) => $sub->where('block_id', $request->block_id)))
            ->when($request->plot_id, fn ($q) => $q->where('plot_sale_detail_id', $request->plot_id))
            ->when($request->customer_id, fn ($q) => $q->whereHas('booking', fn ($sub) => $sub->where('customer_code', $request->customer_id)))
            ->when($request->booking_id, fn ($q) => $q->whereHas('booking', fn ($sub) => $sub->where('booking_code', $request->booking_id)))
            ->when($request->from_date, fn ($q) => $q->whereDate('created_at', '>=', $request->from_date))
            ->when($request->to_date, fn ($q) => $q->whereDate('created_at', '<=', $request->to_date))
            ->latest()
            ->get();
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
        $booking = CustomerBooking::where('associate_id', auth()->user()->id)
            ->whereHas('plotSaleDetail', fn ($q) => $q->where('plot_detail_id', $plotId))
            ->first();

        return $booking ? [
            'customer_id' => $booking->customer_code,
            'booking_id' => $booking->booking_code,
        ] : [];
    }

    public function getTeamBusinessData()
    {
        $associateId = auth()->user()->id;
        $downlineIds = Associate::where('sponsor_id', $associateId)
            ->pluck('associate_id')
            ->toArray();
        $downlineIds[] = $associateId;

        return CustomerBooking::with([
            'associate',
            'primaryDetail',
            'plotSaleDetail.plotDetail.block.project',
        ])
            ->whereIn('associate_id', $downlineIds)->latest()
            ->get()
            ->map(function ($booking) {
                $plotDetail = $booking->plotSaleDetail?->plotDetail;

                return (object) [
                    'booking_code' => $booking->booking_code,
                    'customer_name' => $booking->primaryDetail?->name ?? '-',
                    'agent_name' => $booking->associate?->associate_name ?? '-',
                    'project_name' => $plotDetail?->block?->project?->name ?? '-',
                    'plot_no' => $plotDetail?->plot_number ?? '-',
                    'amount' => $booking->plotSaleDetail?->plot_cost ?? 0,
                    'date' => $booking->created_at?->format('d-m-Y'),
                ];
            });
    }

    public function getDueEmiAmountData()
    {
        $associateId = auth()->user()->id;
        $downlineIds = Associate::where('id', $associateId)
            ->pluck('id')
            ->toArray();
        $downlineIds[] = $associateId;

        return CustomerBooking::with([
            'associate',
            'primaryDetail',
            'payment',
            'plotSaleDetail.plotDetail.block.project',
        ])
            ->whereIn('associate_id', $downlineIds)
            ->latest()
            ->get()
            ->map(function ($booking) {
                $payment = $booking->payment;
                if (! $payment || $payment->plan_type !== 'emi_plan') {
                    return null;
                }
                $plotSale = $booking->plotSaleDetail;
                $plot = $plotSale?->plotDetail;
                $block = $plot?->block;
                $project = $block?->project;
                $allPayments = CustomerPayment::where('customer_booking_id', $booking->id)
                    ->orderBy('id')->get();
                $bookingPayment = $allPayments->where('transaction_category', 'booking_fee')->first();
                $emiPayments = $allPayments->where('transaction_category', 'emi_payment');

                $totalInstallments = (int) ($bookingPayment?->emi_months ?? 0);
                $currentDueAmount = (float) ($payment->due_amount ?? 0);
                $monthlyEmi = (float) ($bookingPayment?->after_booking_payable_amount ?? 0);
                $paidInstallments = $emiPayments->count();
                $remainingInstallments = max(0, $totalInstallments - $paidInstallments);

                $emiHistory = [];
                for ($i = 1; $i <= $totalInstallments; $i++) {
                    $paidEmi = $emiPayments->values()->get($i - 1);

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
                    'project_name' => $project?->name ?? '-',
                    'block_name' => $block?->block ?? '-',
                    'plot_no' => $plot?->plot_number ?? '-',
                    'plot_amount' => round((float) ($plotSale?->plot_cost ?? 0), 2),
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
}
