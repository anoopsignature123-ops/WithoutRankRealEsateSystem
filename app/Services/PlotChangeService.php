<?php

namespace App\Services;

use App\Models\Block;
use App\Models\CustomerPayment;
use App\Models\PlotChangeHistory;
use App\Models\PlotDetail;
use App\Models\PlotSaleDetail;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PlotChangeService
{
    public function index()
    {
        $projects = Project::select('id', 'name')
            ->orderBy('name')
            ->get();

        $histories = PlotChangeHistory::with([
            'customerBooking.primaryDetail',
            'plotSaleDetail',
            'oldProject',
            'oldBlock',
            'oldPlot',
            'newProject',
            'newBlock',
            'newPlot',
            'changedBy',
        ])
            ->latest()
            ->get();

        return compact('projects', 'histories');
    }

    public function getBlocks($projectId)
    {
        return Block::where('project_id', $projectId)
            ->select('id', 'block')
            ->orderBy('block')
            ->get();
    }

    public function getBookedPlots($blockId)
    {
        return PlotSaleDetail::with([
            'plotDetail',
            'customerBooking.primaryDetail',
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
            ->map(function ($sale) {
                $customerName = $sale->customerBooking?->primaryDetail?->name
                    ?? $sale->customerBooking?->customer_name
                    ?? 'N/A';

                return [
                    'id' => $sale->plot_detail_id,
                    'plot_number' => ($sale->plotDetail?->plot_number ?? 'N/A')
                        .' | '.($sale->booking_code ?? 'N/A')
                        .' | '.$customerName,
                ];
            })
            ->sortBy('plot_number')
            ->values();
    }

    public function getAvailablePlots($blockId)
    {
        return PlotDetail::where('block_id', $blockId)
            ->where('status', 'available')
            ->select([
                'id',
                'plot_number',
                'plot_area',
                'plot_rate',
                'plc_rate',
            ])
            ->orderBy('plot_number')
            ->get()
            ->map(function ($plot) {
                return [
                    'id' => $plot->id,
                    'plot_number' => ($plot->plot_number ?? 'N/A')
                        .' | Area '.number_format((float) ($plot->plot_area ?? 0), 2)
                        .' | Rate '.number_format((float) ($plot->plot_rate ?? 0), 2),
                    'plot_area' => $plot->plot_area,
                    'plot_rate' => $plot->plot_rate,
                    'plc_rate' => $plot->plc_rate,
                ];
            })
            ->values();
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
            ->first();

        if (! $plotSale) {
            return [
                'status' => false,
                'message' => 'Booking not found.',
            ];
        }

        $payments = $plotSale->payments;

        $totalPaid = (float) $payments
            ->whereIn('payment_status', ['paid', 'cleared'])
            ->sum('paid_amount');

        $oldTotalCost = (float) ($plotSale->total_plot_cost ?? 0);

        $oldDueAmount = max(0, $oldTotalCost - $totalPaid);

        return [
            'status' => true,

            'plot_sale_detail_id' => $plotSale->id,
            'customer_booking_id' => $plotSale->customer_booking_id,

            'booking_code' => $plotSale->booking_code ?? 'N/A',
            'customer_code' => $plotSale->customerBooking->customer_code ?? '',
            'customer_name' => $plotSale->customerBooking->primaryDetail->name
                ?? $plotSale->customerBooking->customer_name
                ?? '',

            'old_project_id' => $plotSale->project_id,
            'old_block_id' => $plotSale->block_id,
            'old_plot_detail_id' => $plotSale->plot_detail_id,

            'old_project_name' => $plotSale->project->name ?? '',
            'old_block_name' => $plotSale->block->block ?? '',
            'old_plot_number' => $plotSale->plotDetail->plot_number ?? '',

            'old_plot_rate' => number_format((float) ($plotSale->plot_rate ?? 0), 2),
            'old_plot_area' => number_format((float) ($plotSale->plot_area ?? 0), 2),
            'old_plot_cost' => number_format((float) ($plotSale->plot_cost ?? 0), 2),
            'old_plc_amount' => number_format((float) ($plotSale->plc_amount ?? 0), 2),
            'old_total_plot_cost' => number_format($oldTotalCost, 2),

            'total_paid_amount' => number_format($totalPaid, 2),
            'old_due_amount' => number_format($oldDueAmount, 2),
        ];
    }

    public function getNewPlotData($plotId)
    {
        $plot = PlotDetail::with(['project', 'block'])
            ->where('id', $plotId)
            ->where('status', 'available')
            ->first();

        if (! $plot) {
            return [
                'status' => false,
                'message' => 'Available plot not found.',
            ];
        }

        $plotArea = (float) ($plot->plot_area ?? 0);
        $plotRate = (float) ($plot->plot_rate ?? 0);
        $plcAmount = (float) ($plot->plc_rate ?? 0);

        $plotCost = $plotArea * $plotRate;
        $totalPlotCost = $plotCost + $plcAmount;

        return [
            'status' => true,

            'new_project_id' => $plot->project_id,
            'new_block_id' => $plot->block_id,
            'new_plot_detail_id' => $plot->id,

            'new_project_name' => $plot->project->name ?? '',
            'new_block_name' => $plot->block->block ?? '',
            'new_plot_number' => $plot->plot_number ?? '',

            'new_plot_area' => number_format($plotArea, 2),
            'new_plot_rate' => number_format($plotRate, 2),
            'new_plot_cost' => number_format($plotCost, 2),
            'new_plc_amount' => number_format($plcAmount, 2),
            'new_total_plot_cost' => number_format($totalPlotCost, 2),
        ];
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $plotSale = PlotSaleDetail::with([
                'customerBooking.primaryDetail',
                'project',
                'block',
                'plotDetail',
                'payments',
            ])
                ->where('status', 'active')
                ->findOrFail($data['plot_sale_detail_id']);

            $oldPlotId = $plotSale->plot_detail_id;

            $newPlot = PlotDetail::where('id', $data['new_plot_detail_id'])
                ->where('status', 'available')
                ->firstOrFail();

            if ($oldPlotId == $newPlot->id) {
                throw new \Exception('The selected new plot is the same as the current plot.');
            }

            if ((int) $newPlot->project_id !== (int) $data['new_project_id'] || (int) $newPlot->block_id !== (int) $data['new_block_id']) {
                throw new \Exception('Selected new plot does not belong to the selected project and block.');
            }

            $payments = $plotSale->payments;

            $totalPaid = (float) $payments
                ->whereIn('payment_status', ['paid', 'cleared'])
                ->sum('paid_amount');

            $oldTotalCost = (float) ($plotSale->total_plot_cost ?? 0);
            $oldDueAmount = max(0, $oldTotalCost - $totalPaid);

            $newPlotArea = (float) ($newPlot->plot_area ?? 0);
            $newPlotRate = (float) ($newPlot->plot_rate ?? 0);
            $newPlcAmount = (float) ($newPlot->plc_rate ?? 0);

            $newPlotCost = $newPlotArea * $newPlotRate;
            $newTotalCost = $newPlotCost + $newPlcAmount;

            $newDueAmount = max(0, $newTotalCost - $totalPaid);
            $differenceAmount = $newTotalCost - $oldTotalCost;

            PlotChangeHistory::create([
                'customer_booking_id' => $plotSale->customer_booking_id,
                'plot_sale_detail_id' => $plotSale->id,

                'old_project_id' => $plotSale->project_id,
                'old_block_id' => $plotSale->block_id,
                'old_plot_detail_id' => $plotSale->plot_detail_id,

                'new_project_id' => $data['new_project_id'],
                'new_block_id' => $data['new_block_id'],
                'new_plot_detail_id' => $newPlot->id,

                'old_plot_rate' => $plotSale->plot_rate ?? 0,
                'old_plot_area' => $plotSale->plot_area ?? 0,
                'old_plot_cost' => $plotSale->plot_cost ?? 0,
                'old_plc_amount' => $plotSale->plc_amount ?? 0,
                'old_total_plot_cost' => $oldTotalCost,

                'new_plot_rate' => $newPlotRate,
                'new_plot_area' => $newPlotArea,
                'new_plot_cost' => $newPlotCost,
                'new_plc_amount' => $newPlcAmount,
                'new_total_plot_cost' => $newTotalCost,

                'total_paid_amount' => $totalPaid,
                'old_due_amount' => $oldDueAmount,
                'new_due_amount' => $newDueAmount,
                'difference_amount' => $differenceAmount,

                'change_date' => $data['change_date'] ?? now()->toDateString(),
                'change_reason' => $data['change_reason'] ?? null,
                'remark' => $data['remark'] ?? null,

                'changed_by' => Auth::id(),
            ]);

            PlotDetail::where('id', $oldPlotId)->update([
                'status' => 'available',
            ]);

            PlotDetail::where('id', $newPlot->id)->update([
                'status' => 'booked',
            ]);

            $plotSale->update([
                'project_id' => $data['new_project_id'],
                'block_id' => $data['new_block_id'],
                'plot_detail_id' => $newPlot->id,

                'plot_rate' => $newPlotRate,
                'plot_area' => $newPlotArea,
                'plot_cost' => $newPlotCost,
                'plc_amount' => $newPlcAmount,
                'total_plot_cost' => $newTotalCost,
                'final_payable' => $newTotalCost,
            ]);

            $latestPayment = CustomerPayment::where('plot_sale_detail_id', $plotSale->id)
                ->latest()
                ->first();

            if ($latestPayment) {
                $latestPayment->update([
                    'due_amount' => $newDueAmount,
                    'net_payable_amount' => $newDueAmount,
                ]);
            }

            return true;
        });
    }
}