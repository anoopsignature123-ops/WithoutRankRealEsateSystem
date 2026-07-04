<?php

namespace App\Services;

use App\Models\Block;
use App\Models\CustomerBooking;
use App\Models\CustomerPayment;
use App\Models\PaymentTransferHistory;
use App\Models\PlotSaleDetail;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentTransferService
{
    public function index()
    {
        $projects = Project::select('id', 'name')
            ->orderBy('name')
            ->get();

        $histories = PaymentTransferHistory::with([
            'customerPayment',
            'oldCustomerBooking.primaryDetail',
            'newCustomerBooking.primaryDetail',
            'oldPlotSaleDetail.project',
            'oldPlotSaleDetail.block',
            'oldPlotSaleDetail.plotDetail',
            'newPlotSaleDetail.project',
            'newPlotSaleDetail.block',
            'newPlotSaleDetail.plotDetail',
            'createdBy',
        ])
            ->latest()
            ->get()
            ->groupBy(function ($history) {
                return implode('|', [
                    $history->old_customer_booking_id,
                    $history->new_customer_booking_id,
                    $history->old_booking_code ?: 'old-'.$history->old_plot_sale_detail_id,
                    $history->new_booking_code ?: 'new-'.$history->new_plot_sale_detail_id,
                    optional($history->transfer_date)->format('Y-m-d'),
                    $history->created_by ?: 'system',
                    $history->created_at?->format('Y-m-d H:i:s') ?: $history->id,
                ]);
            })
            ->map(function ($group) {
                $first = $group->first();
                $oldPlotSales = $group->pluck('oldPlotSaleDetail')->filter();
                $newPlotSales = $group->pluck('newPlotSaleDetail')->filter();

                $first->group_receipts = $group
                    ->map(fn ($history) => $history->customerPayment?->receipt_number)
                    ->filter()
                    ->unique()
                    ->implode(', ');
                $first->group_payment_count = $group->count();
                $first->group_transfer_amount = round((float) $group->sum('transfer_amount'), 2);
                $first->group_old_plots = $oldPlotSales
                    ->map(fn ($sale) => $sale?->plotDetail?->plot_number)
                    ->filter()
                    ->unique()
                    ->implode(', ');
                $first->group_new_plots = $newPlotSales
                    ->map(fn ($sale) => $sale?->plotDetail?->plot_number)
                    ->filter()
                    ->unique()
                    ->implode(', ');

                return $first;
            })
            ->values();

        return compact('projects', 'histories');
    }

    public function getBlocks($projectId)
    {
        return Block::where('project_id', $projectId)
            ->select('id', 'block')
            ->orderBy('block')
            ->get();
    }

    public function getPlots($blockId)
    {
        return PlotSaleDetail::with([
            'plotDetail',
            'customerBooking.primaryDetail',
            'customerBooking.plotSaleDetails.plotDetail',
        ])
            ->where('block_id', $blockId)
            ->where('status', 'active')
            ->whereHas('payments')
            ->get()
            ->groupBy(function ($sale) {
                return $sale->customer_booking_id.'|'.($sale->booking_code ?: 'plot-'.$sale->id);
            })
            ->map(function ($sales) {
                $representativeSale = $sales->first();
                $booking = $representativeSale->customerBooking;
                $bookingPlots = $representativeSale->booking_code && $booking
                    ? $booking->plotSaleDetails->where('booking_code', $representativeSale->booking_code)->values()
                    : $sales->values();
                $plotNumbers = $bookingPlots
                    ->map(fn ($sale) => $sale->plotDetail?->plot_number)
                    ->filter()
                    ->unique()
                    ->values();

                return [
                    'id' => $representativeSale->plot_detail_id,
                    'plot_number' => $plotNumbers->count() > 1
                        ? $plotNumbers->implode(', ').' (Multiple - '.$plotNumbers->count().' Plots)'
                        : ($plotNumbers->first() ?: 'Plot #'.$representativeSale->plot_detail_id),
                    'booking_code' => $representativeSale->booking_code,
                    'customer_name' => $booking?->primaryDetail?->name ?? $booking?->customer_name,
                    'is_multiple' => $plotNumbers->count() > 1,
                ];
            })
            ->sortBy('plot_number')
            ->values();
    }

    public function getPayments($plotId)
    {
        $plotSale = PlotSaleDetail::with([
            'project',
            'block',
            'plotDetail',
            'customerBooking.primaryDetail',
            'payments',
        ])
            ->where('plot_detail_id', $plotId)
            ->where('status', 'active')
            ->first();

        if (! $plotSale) {
            return [
                'status' => false,
                'message' => 'Plot sale details not found.',
            ];
        }

        $booking = $plotSale->customerBooking;
        $groupPlotSales = $plotSale->booking_code && $booking
            ? $booking->plotSaleDetails()
                ->with(['project', 'block', 'plotDetail'])
                ->where('booking_code', $plotSale->booking_code)
                ->where('status', 'active')
                ->get()
            : collect([$plotSale]);

        if ($groupPlotSales->isEmpty()) {
            $groupPlotSales = collect([$plotSale]);
        }

        $payments = CustomerPayment::with('plotSaleDetail.plotDetail')
            ->where('customer_booking_id', $plotSale->customer_booking_id)
            ->whereIn('plot_sale_detail_id', $groupPlotSales->pluck('id'))
            ->orderBy('id')
            ->get()
            ->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'plot_no' => $payment->plotSaleDetail?->plotDetail?->plot_number ?? '-',
                    'receipt_number' => $payment->receipt_number ?? '-',
                    'date' => $payment->created_at
                        ? $payment->created_at->format('d-m-Y')
                        : '-',
                    'plan_type' => $payment->plan_type ?? '-',
                    'transaction_category' => $payment->transaction_category ?? '-',
                    'payment_mode' => strtoupper(str_replace('_', '/', $payment->payment_mode ?? '-')),
                    'booking_status' => ucfirst($payment->booking_status ?? '-'),
                    'payment_status' => ucfirst($payment->payment_status ?? '-'),
                    'paid_amount' => number_format((float) ($payment->paid_amount ?? 0), 2),
                    'paid_amount_raw' => round((float) ($payment->paid_amount ?? 0), 2),
                ];
            });

        return [
            'status' => true,

            'plot_sale_id' => $plotSale->id,
            'plot_sale_ids' => $groupPlotSales->pluck('id')->values(),
            'booking_code' => $plotSale->booking_code ?? 'N/A',

            'customer_booking_id' => $plotSale->customer_booking_id,
            'customer_code' => $plotSale->customerBooking->customer_code ?? '',
            'customer_name' => $plotSale->customerBooking->primaryDetail->name
                ?? $plotSale->customerBooking->customer_name
                ?? '',

            'project_name' => $groupPlotSales->map(fn ($sale) => $sale->project?->name)->filter()->unique()->implode(', '),
            'block_name' => $groupPlotSales->map(fn ($sale) => $sale->block?->block)->filter()->unique()->implode(', '),
            'plot_number' => $groupPlotSales->map(fn ($sale) => $sale->plotDetail?->plot_number)->filter()->unique()->implode(', '),
            'plot_count' => $groupPlotSales->count(),

            'payments' => $payments,
        ];
    }

    public function getCustomers()
    {
        return CustomerBooking::with('primaryDetail')
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('customer_code')
            ->whereHas('plotSaleDetails', function ($query) {
                $query->whereNotNull('booking_code')->where('status', 'active');
                ;
            })
            ->orderBy('customer_code')
            ->get()
            ->map(function ($customer) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->customer_code . ' - ' . (
                        $customer->primaryDetail->name
                        ?? $customer->customer_name
                        ?? 'N/A'
                    ),
                ];
            });
    }

    public function getCustomerPlots($customerBookingId)
    {
        return PlotSaleDetail::with([
            'project',
            'block',
            'plotDetail',
        ])
            ->where('customer_booking_id', $customerBookingId)
            ->whereNotNull('booking_code')
            ->where('status', 'active')
            ->orderByDesc('id')
            ->get()
            ->groupBy(fn ($plotSale) => $plotSale->booking_code ?: 'plot-'.$plotSale->id)
            ->map(function ($plotSales) {
                $plotSale = $plotSales->first();
                $projects = $plotSales
                    ->map(fn ($sale) => $sale->project?->name)
                    ->filter()
                    ->unique()
                    ->implode(', ');
                $blocks = $plotSales
                    ->map(fn ($sale) => $sale->block?->block)
                    ->filter()
                    ->unique()
                    ->implode(', ');
                $plotNumbers = $plotSales
                    ->map(fn ($sale) => $sale->plotDetail?->plot_number)
                    ->filter()
                    ->unique()
                    ->implode(', ');

                return [
                    'id' => $plotSale->id,
                    'name' => ($plotSale->booking_code ?? 'N/A')
                        . ' | '
                        . ($projects ?: 'Project')
                        . ' / '
                        . 'Block '.$blocks
                        . ' / Plot '
                        . ($plotNumbers ?: 'N/A')
                        . ($plotSales->count() > 1 ? ' ('.$plotSales->count().' Plots)' : ''),
                ];
            })
            ->values();
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {

            $newBooking = CustomerBooking::with('primaryDetail')
                ->findOrFail($data['new_customer_booking_id']);

            $newPlotSale = PlotSaleDetail::with([
                'project',
                'block',
                'plotDetail',
            ])->where('status', 'active')->findOrFail($data['new_plot_sale_detail_id']);

            if ($newPlotSale->customer_booking_id != $newBooking->id) {
                throw new \Exception('Selected plot does not belong to selected customer.');
            }

            $payments = CustomerPayment::with([
                'customerBooking.primaryDetail',
                'plotSaleDetail',
            ])
                ->whereIn('id', $data['payment_ids'])
                ->get();

            if ($payments->count() !== count(array_unique($data['payment_ids']))) {
                throw new \Exception('One or more selected payments were not found.');
            }

            $sourceCustomerBookingIds = $payments->pluck('customer_booking_id')->unique();
            $sourcePlotSaleIds = $payments->pluck('plot_sale_detail_id')->unique();

            if (
                $sourceCustomerBookingIds->count() === 1
                && (int) $sourceCustomerBookingIds->first() === (int) $newBooking->id
                && $sourcePlotSaleIds->contains((int) $newPlotSale->id)
            ) {
                throw new \Exception('Payment cannot be transferred to the same customer and same plot.');
            }

            foreach ($payments as $payment) {

                $oldBooking = $payment->customerBooking;
                $oldPlotSale = $payment->plotSaleDetail;

                if (! $oldBooking || ! $oldPlotSale) {
                    throw new \Exception('Invalid old payment record found.');
                }

                if (
                    $payment->customer_booking_id == $newBooking->id &&
                    $payment->plot_sale_detail_id == $newPlotSale->id
                ) {
                    throw new \Exception('Selected payment is already linked with selected customer plot booking.');
                }

                PaymentTransferHistory::create([
                    'customer_payment_id' => $payment->id,

                    'old_customer_booking_id' => $oldBooking->id,
                    'new_customer_booking_id' => $newBooking->id,

                    'old_plot_sale_detail_id' => $oldPlotSale->id,
                    'new_plot_sale_detail_id' => $newPlotSale->id,

                    'old_booking_code' => $oldPlotSale->booking_code,
                    'new_booking_code' => $newPlotSale->booking_code,

                    'old_customer_code' => $oldBooking->customer_code,
                    'new_customer_code' => $newBooking->customer_code,

                    'old_customer_name' => $oldBooking->primaryDetail->name ?? $oldBooking->customer_name,
                    'new_customer_name' => $newBooking->primaryDetail->name ?? $newBooking->customer_name,

                    'transfer_amount' => $payment->paid_amount ?? $payment->booking_amount ?? 0,
                    'transfer_date' => $data['transfer_date'] ?? now()->toDateString(),
                    'transfer_reason' => $data['transfer_reason'] ?? null,
                    'remark' => $data['remark'] ?? null,

                    'created_by' => Auth::id(),
                ]);

                $payment->update([
                    'customer_booking_id' => $newBooking->id,
                    'plot_sale_detail_id' => $newPlotSale->id,
                ]);
            }

            app(AutoPromotionService::class)->runForBooking($newBooking);

            return true;
        });
    }
}
