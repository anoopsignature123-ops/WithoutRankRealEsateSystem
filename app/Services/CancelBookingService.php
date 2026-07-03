<?php

namespace App\Services;

use App\Models\CancelBooking;
use App\Models\CustomerPayment;
use App\Models\PlotDetail;
use App\Models\PlotSaleDetail;
use App\Models\Project;
use Exception;
use Illuminate\Support\Facades\DB;

class CancelBookingService
{
    public function index()
    {
        $projects = Project::select('id', 'name')
            ->orderBy('name')
            ->get();

        $plotSales = PlotSaleDetail::with([
            'project',
            'block',
            'plotDetail',
            'customerBooking.primaryDetail',
            'payments',
        ])
            ->whereNotNull('booking_code')
            ->where('status', 'active')
            ->whereHas('customerBooking', function ($query) {
                $query->where('status', '!=', 'cancelled');
            })
            ->whereHas('plotDetail', function ($query) {
                $query->whereIn('status', ['booked', 'hold']);
            })
            ->whereHas('payments', function ($query) {
                $query->where('booking_status', 'booked')
                    ->whereIn('payment_status', ['paid', 'cleared']);
            })
            ->latest()
            ->get();

        $cancelHistories = CancelBooking::with([
            'customerBooking.primaryDetail',
            'plotSaleDetail.project',
            'plotSaleDetail.block',
            'plotSaleDetail.plotDetail',
        ])
            ->latest()
            ->get();

        return compact('projects', 'plotSales', 'cancelHistories');
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {

            $plotSale = PlotSaleDetail::with([
                'customerBooking',
                'payments',
                'plotDetail',
            ])->findOrFail($data['plot_sale_detail_id']);

            $booking = $plotSale->customerBooking;

            if (! $booking) {
                throw new Exception('Customer booking not found.');
            }

            if ($plotSale->status !== 'active') {
                throw new Exception('Selected plot sale is not active.');
            }

            $hasBookedPayment = $plotSale->payments->contains(function ($payment) {
                return $payment->booking_status === 'booked'
                    && in_array($payment->payment_status, ['paid', 'cleared']);
            });

            if (!$hasBookedPayment) {
                throw new Exception('This plot does not have booked/confirmed payment.');
            }

            $groupPlotSales = $plotSale->booking_code
                ? PlotSaleDetail::with(['payments', 'plotDetail'])
                    ->where('customer_booking_id', $booking->id)
                    ->where('booking_code', $plotSale->booking_code)
                    ->where('status', 'active')
                    ->whereHas('plotDetail', function ($query) {
                        $query->whereIn('status', ['booked', 'hold']);
                    })
                    ->whereHas('payments', function ($query) {
                        $query->where('booking_status', 'booked')
                            ->whereIn('payment_status', ['paid', 'cleared']);
                    })
                    ->get()
                : collect([$plotSale]);

            if ($groupPlotSales->isEmpty()) {
                $groupPlotSales = collect([$plotSale]);
            }

            $selectedPlotSaleIds = collect($data['plot_sale_detail_ids'] ?? [])
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique()
                ->values();

            if ($selectedPlotSaleIds->isNotEmpty()) {
                $allowedIds = $groupPlotSales->pluck('id')->map(fn ($id) => (int) $id);
                $invalidIds = $selectedPlotSaleIds->diff($allowedIds);

                if ($invalidIds->isNotEmpty()) {
                    throw new Exception('Selected plot does not belong to this booking group.');
                }

                $groupPlotSales = $groupPlotSales
                    ->whereIn('id', $selectedPlotSaleIds->all())
                    ->values();
            }

            if ($groupPlotSales->isEmpty()) {
                throw new Exception('Please select at least one plot for cancellation.');
            }

            $plotSaleIds = $groupPlotSales->pluck('id')->values();

            $totalPaid = $groupPlotSales->sum(function ($sale) {
                return $sale->payments
                    ->where('booking_status', 'booked')
                    ->whereIn('payment_status', ['paid', 'cleared'])
                    ->sum(function ($payment) {
                        return (float) ($payment->paid_amount ?? $payment->booking_amount ?? 0);
                    });
            });

            $totalDeduction = round((float) ($data['deduction_amount'] ?? 0), 2);
            $totalRefund = round((float) ($data['refund_amount'] ?? 0), 2);

            if ($totalRefund + $totalDeduction > $totalPaid) {
                throw new Exception('Refund and deduction amount cannot be greater than paid amount.');
            }

            $paymentStatusAfterCancel = $totalRefund > 0 ? 'refunded' : 'cancelled';

            $allocatedDeduction = 0.0;
            $allocatedRefund = 0.0;
            $lastPlotIndex = $groupPlotSales->count() - 1;

            foreach ($groupPlotSales as $index => $sale) {
                $plotPaid = $sale->payments
                    ->where('booking_status', 'booked')
                    ->whereIn('payment_status', ['paid', 'cleared'])
                    ->sum(function ($payment) {
                        return (float) ($payment->paid_amount ?? $payment->booking_amount ?? 0);
                    });

                $ratio = $totalPaid > 0
                    ? ($plotPaid / $totalPaid)
                    : (1 / max($groupPlotSales->count(), 1));

                $plotDeduction = $index === $lastPlotIndex
                    ? round($totalDeduction - $allocatedDeduction, 2)
                    : round($totalDeduction * $ratio, 2);

                $plotRefund = $index === $lastPlotIndex
                    ? round($totalRefund - $allocatedRefund, 2)
                    : round($totalRefund * $ratio, 2);

                $allocatedDeduction = round($allocatedDeduction + $plotDeduction, 2);
                $allocatedRefund = round($allocatedRefund + $plotRefund, 2);

                CancelBooking::create([
                    'customer_booking_id' => $booking->id,
                    'plot_sale_detail_id' => $sale->id,
                    'deduction_amount' => $plotDeduction,
                    'deduction_percentage' => $data['deduction_percentage'] ?? null,
                    'refund_amount' => $plotRefund,
                    'pay_mode' => $data['pay_mode'] ?? null,
                    'pay_date' => $data['pay_date'] ?? null,
                    'bank_name' => $data['bank_name'] ?? null,
                    'account_number' => $data['account_number'] ?? null,
                    'ifsc_code' => $data['ifsc_code'] ?? null,
                    'cheque_date' => $data['cheque_date'] ?? null,
                ]);

                CustomerPayment::where('customer_booking_id', $booking->id)
                    ->where('plot_sale_detail_id', $sale->id)
                    ->where('booking_status', 'booked')
                    ->update([
                        'booking_status' => 'cancelled',
                        'payment_status' => $paymentStatusAfterCancel,
                    ]);

                $sale->update([
                    'status' => 'cancelled',
                ]);

                if ($sale->plot_detail_id) {
                    PlotDetail::where('id', $sale->plot_detail_id)->update([
                        'status' => 'available',
                    ]);
                }
            }

            $activePlotCount = PlotSaleDetail::where('customer_booking_id', $booking->id)
                ->whereNotIn('id', $plotSaleIds)
                ->whereNotNull('booking_code')
                ->where('status', 'active')
                ->whereHas('plotDetail', function ($query) {
                    $query->whereIn('status', ['booked', 'hold']);
                })
                ->whereHas('payments', function ($query) {
                    $query->where('booking_status', 'booked')
                        ->whereIn('payment_status', ['paid', 'cleared']);
                })
                ->count();

            if ($activePlotCount <= 0) {
                $booking->update([
                    'status' => 'cancelled',
                ]);
            }

            return true;
        });
    }
}