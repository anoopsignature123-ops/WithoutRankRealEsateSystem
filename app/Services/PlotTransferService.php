<?php

namespace App\Services;

use App\Models\Block;
use App\Models\CustomerBooking;
use App\Models\CustomerPayment;
use App\Models\PlotSaleDetail;
use App\Models\PlotTransferHistory;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PlotTransferService
{
    public function index($data)
    {
        $projects = Project::select('id', 'name')
            ->orderBy('name')
            ->get();

        $histories = PlotTransferHistory::with([
            'plotSaleDetail.project',
            'plotSaleDetail.block',
            'plotSaleDetail.plotDetail',
            'oldBooking.primaryDetail',
            'newBooking.primaryDetail',
            'createdBy',
        ])
            ->latest()
            ->get()
            ->groupBy(function ($history) {
                $bookingCode = $history->plotSaleDetail?->booking_code ?: 'history-'.$history->id;

                return implode('|', [
                    $history->old_booking_id,
                    $history->new_booking_id,
                    $bookingCode,
                    optional($history->transfer_date)->format('Y-m-d'),
                    $history->created_by ?: 'system',
                    $history->created_at?->format('Y-m-d H:i:s') ?: $history->id,
                ]);
            })
            ->map(function ($group) {
                $first = $group->first();
                $plotSales = $group->pluck('plotSaleDetail')->filter();

                $first->group_plot_count = $plotSales->count();
                $first->group_projects = $plotSales
                    ->map(fn ($sale) => $sale?->project?->name)
                    ->filter()
                    ->unique()
                    ->implode(', ');
                $first->group_blocks = $plotSales
                    ->map(fn ($sale) => $sale?->block?->block)
                    ->filter()
                    ->unique()
                    ->implode(', ');
                $first->group_plot_numbers = $plotSales
                    ->map(fn ($sale) => $sale?->plotDetail?->plot_number)
                    ->filter()
                    ->unique()
                    ->implode(', ');
                $first->group_transfer_charge = round((float) $group->sum('transfer_charge'), 2);

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
            'customerBooking.plotSaleDetails.block',
        ])
            ->where('block_id', $blockId)
            ->where('status', 'active')
            ->whereHas('plotDetail', function ($query) {
                $query->where('status', 'booked');
            })
            ->whereHas('customerBooking', function ($query) {
                $query->where('status', '!=', 'cancelled');
            })
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
                $plotLabel = $plotNumbers->implode(', ');

                return [
                    'id' => $representativeSale->plot_detail_id,
                    'plot_number' => $plotNumbers->count() > 1
                        ? $plotLabel.' (Multiple - '.$plotNumbers->count().' Plots)'
                        : ($plotLabel ?: 'Plot #'.$representativeSale->plot_detail_id),
                    'booking_code' => $representativeSale->booking_code,
                    'customer_name' => $booking?->primaryDetail?->name ?? $booking?->customer_name,
                    'is_multiple' => $plotNumbers->count() > 1,
                ];
            })
            ->sortBy('plot_number')
            ->values();
    }

    public function getTransferCustomers($bookingId)
    {
        return CustomerBooking::with('primaryDetail')
            ->where('id', '!=', $bookingId)
            ->where('status', '!=', 'cancelled')
            ->whereNotNull('customer_code')
            ->orderBy('customer_code')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->customer_code.' - '.(
                        $item->primaryDetail->name
                        ?? $item->customer_name
                        ?? 'N/A'
                    ),
                ];
            });
    }

    public function getBookingData($plotId)
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
            ->whereHas('customerBooking', function ($query) {
                $query->where('status', '!=', 'cancelled');
            })
            ->first();

        if (! $plotSale) {
            return null;
        }

        $booking = $plotSale->customerBooking;
        $groupPlotSales = $plotSale->booking_code && $booking
            ? $booking->plotSaleDetails()
                ->with(['project', 'block', 'plotDetail', 'payments'])
                ->where('booking_code', $plotSale->booking_code)
                ->where('status', 'active')
                ->get()
            : collect([$plotSale]);

        if ($groupPlotSales->isEmpty()) {
            $groupPlotSales = collect([$plotSale]);
        }

        $payments = CustomerPayment::where('customer_booking_id', $plotSale->customer_booking_id)
            ->whereIn('plot_sale_detail_id', $groupPlotSales->pluck('id'))
            ->get();

        $latestPayment = $payments
            ->sortByDesc('id')
            ->first();

        $bookingPayment = $payments
            ->where('transaction_category', 'booking_fee')
            ->sortBy('id')
            ->first();

        $totalPlotCost = (float) $groupPlotSales->sum(fn ($sale) => (float) ($sale->total_plot_cost ?? 0));

        $totalPaid = (float) $payments
            ->whereIn('payment_status', ['paid', 'cleared'])
            ->sum('paid_amount');

        $remainingAmount = max(0, $totalPlotCost - $totalPaid);

        $planTypes = $payments->pluck('plan_type')->filter()->unique()->values();
        $planType = $planTypes->count() === 1 ? $planTypes->first() : 'mixed';

        $emiMonths = 0;
        $paidEmis = 0;
        $dueMonths = 0;

        if ($planType === 'emi_plan') {
            $emiMonths = (int) ($latestPayment->emi_months ?? 0);

            $paidEmis = $payments
                ->where('transaction_category', 'emi_payment')
                ->whereIn('payment_status', ['paid', 'cleared'])
                ->count();

            $dueMonths = max(0, $emiMonths - $paidEmis);
        }

        return [
            'booking_id' => $plotSale->booking_code ?? 'N/A',

            'customer_id' => $plotSale->customerBooking->customer_code ?? '',
            'customer_name' => $plotSale->customerBooking->primaryDetail->name
                ?? $plotSale->customerBooking->customer_name
                ?? '',

            'plot_sale_id' => $plotSale->id,
            'customer_booking_id' => $plotSale->customer_booking_id,

            'project_name' => $groupPlotSales->map(fn ($sale) => $sale->project?->name)->filter()->unique()->implode(', '),
            'block_name' => $groupPlotSales->map(fn ($sale) => $sale->block?->block)->filter()->unique()->implode(', '),
            'plot_number' => $groupPlotSales->map(fn ($sale) => $sale->plotDetail?->plot_number)->filter()->unique()->implode(', '),
            'plot_area' => number_format((float) $groupPlotSales->sum(fn ($sale) => (float) ($sale->plot_area ?? 0)), 2),
            'plot_rate' => $groupPlotSales->count() > 1 ? 'Multiple' : ($plotSale->plot_rate ?? 0),
            'total_plot_cost' => number_format($totalPlotCost, 2),
            'plot_count' => $groupPlotSales->count(),
            'plots' => $groupPlotSales->map(fn ($sale) => [
                'plot_sale_id' => $sale->id,
                'project' => $sale->project?->name ?? '-',
                'block' => $sale->block?->block ?? '-',
                'plot_number' => $sale->plotDetail?->plot_number ?? '-',
                'area' => number_format((float) ($sale->plot_area ?? 0), 2),
                'rate' => number_format((float) ($sale->plot_rate ?? 0), 2),
                'total_cost' => number_format((float) ($sale->total_plot_cost ?? 0), 2),
            ])->values(),

            'plan_type' => $planType,
            'booking_amount' => number_format((float) ($bookingPayment->booking_amount ?? 0), 2),
            'total_paid' => number_format($totalPaid, 2),
            'remaining_amount' => number_format($remainingAmount, 2),

            'emi_months' => $emiMonths,
            'paid_emis' => $paidEmis,
            'due_months' => $dueMonths,

            'payment_status' => ucfirst($latestPayment->payment_status ?? 'pending'),
            'booking_status' => ucfirst($latestPayment->booking_status ?? 'hold'),
            'payment_mode' => ucfirst($latestPayment->payment_mode ?? 'N/A'),
        ];
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {

            $plotSale = PlotSaleDetail::with([
                'customerBooking.primaryDetail',
            ])->findOrFail($data['plot_sale_detail_id']);

            $oldBooking = $plotSale->customerBooking;

            $newBooking = CustomerBooking::with('primaryDetail')
                ->findOrFail($data['new_customer_booking_id']);

            if ($oldBooking->id == $newBooking->id) {
                throw new \Exception('Plot cannot be transferred to the same customer.');
            }

            $groupPlotSales = $plotSale->booking_code
                ? PlotSaleDetail::where('customer_booking_id', $oldBooking->id)
                    ->where('booking_code', $plotSale->booking_code)
                    ->where('status', 'active')
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
                    throw new \Exception('Selected plot does not belong to this booking group.');
                }

                $groupPlotSales = $groupPlotSales
                    ->whereIn('id', $selectedPlotSaleIds->all())
                    ->values();
            }

            if ($groupPlotSales->isEmpty()) {
                throw new \Exception('Please select at least one plot for transfer.');
            }

            $transferCharge = round((float) ($data['transfer_charge'] ?? 0), 2);
            $perPlotTransferCharge = $groupPlotSales->count() > 0
                ? round($transferCharge / $groupPlotSales->count(), 2)
                : $transferCharge;
            $allocatedTransferCharge = 0.0;
            $lastPlotIndex = $groupPlotSales->count() - 1;

            foreach ($groupPlotSales as $index => $sale) {
                $plotTransferCharge = $index === $lastPlotIndex
                    ? round($transferCharge - $allocatedTransferCharge, 2)
                    : $perPlotTransferCharge;
                $allocatedTransferCharge = round($allocatedTransferCharge + $plotTransferCharge, 2);

                PlotTransferHistory::create([
                    'plot_sale_detail_id' => $sale->id,

                    'old_booking_id' => $oldBooking->id,
                    'new_booking_id' => $newBooking->id,

                    'old_customer_code' => $oldBooking->customer_code,
                    'new_customer_code' => $newBooking->customer_code,

                    'old_customer_name' => $oldBooking->primaryDetail->name ?? $oldBooking->customer_name,
                    'new_customer_name' => $newBooking->primaryDetail->name ?? $newBooking->customer_name,

                    'transfer_charge' => $plotTransferCharge,
                    'transfer_date' => $data['transfer_date'] ?? now()->toDateString(),
                    'transfer_reason' => $data['transfer_reason'] ?? null,
                    'remark' => $data['remark'] ?? null,

                    'created_by' => Auth::id(),
                ]);
            }

            // Existing plot owner update
            PlotSaleDetail::whereIn('id', $groupPlotSales->pluck('id'))
                ->update([
                    'customer_booking_id' => $newBooking->id,
                    'status' => 'active',
                ]);

            // Existing payments owner update
            CustomerPayment::whereIn('plot_sale_detail_id', $groupPlotSales->pluck('id'))
                ->update([
                    'customer_booking_id' => $newBooking->id,
                ]);

            app(AutoPromotionService::class)->runForBooking($newBooking);

            return true;
        });
    }
}
