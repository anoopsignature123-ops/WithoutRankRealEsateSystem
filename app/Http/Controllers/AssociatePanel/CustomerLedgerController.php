<?php

namespace App\Http\Controllers\AssociatePanel;

use App\Http\Controllers\Controller;
use App\Models\Block;
use App\Models\CustomerBooking;
use App\Models\CustomerPayment;
use App\Models\PlotDetail;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CustomerLedgerController extends Controller
{
    public function customerLedger(Request $request)
    {
        $projects = Project::latest()->get();
        $blocks = [];
        $plots = [];
        $ledgerData = null;

        if ($request->filled('project_id')) {
            $blocks = Block::where('project_id', $request->project_id)->get();
        }

        if ($request->filled('block_id')) {
            $associateIds = $this->teamAssociateIds();

            $plotIds = CustomerPayment::where('booking_status', 'booked')
                ->whereIn('payment_status', ['paid', 'cleared'])
                ->whereHas('booking', function ($q) use ($associateIds) {
                    $q->whereIn('associate_id', $associateIds);
                })
                ->whereHas('plotSaleDetail', function ($q) use ($request) {
                    $q->where('block_id', $request->block_id)
                        ->where('status', 'active');
                })
                ->with('plotSaleDetail')->get()
                ->pluck('plotSaleDetail.plot_detail_id')->filter()->unique()->values();
            $plots = PlotDetail::whereIn('id', $plotIds)
                ->where('block_id', $request->block_id)->get();
        }

        if ($request->filled('booking_id')) {
            $associateIds = $this->teamAssociateIds();
            $booking = CustomerBooking::with([
                'primaryDetail',
                'associate',
                'plotSaleDetails.project',
                'plotSaleDetails.block',
                'plotSaleDetails.plotDetail',
                'plotSaleDetails.payments',
                'payments.plotSaleDetail.plotDetail',
            ])
                ->whereIn('associate_id', $associateIds)
                ->where('booking_code', $request->booking_id)
                ->whereHas('plotSaleDetails', function ($q) {
                    $q->where('status', 'active')
                        ->whereHas('payments', function ($p) {
                            $p->where('booking_status', 'booked')
                                ->whereIn('payment_status', ['paid', 'cleared']);
                        });
                })->first();

            if ($booking) {
                $plotSales = $booking->plotSaleDetails
                    ->filter(function ($plotSale) {
                        return $plotSale->status === 'active'
                            && $plotSale->payments->contains(function ($payment) {
                                return $payment->booking_status === 'booked'
                                    && in_array($payment->payment_status, ['paid', 'cleared']);
                            });
                    })->values();

                $plotSaleIds = $plotSales->pluck('id')->values();

                $payments = $booking->payments
                    ->whereIn('plot_sale_detail_id', $plotSaleIds)
                    ->where('booking_status', 'booked')
                    ->whereIn('payment_status', ['paid', 'cleared'])
                    ->sortByDesc('id')->values();

                if ($plotSales->isEmpty() || $payments->isEmpty()) {
                    $ledgerData = null;
                } else {
                    $receiptGroups = $this->groupPaymentsByReceipt($payments);
                    $totalPlotCost = (float) $plotSales->sum(
                        fn($plotSale) => $plotSale->total_plot_cost
                        ?? $plotSale->final_payable ?? $plotSale->plot_cost ?? 0
                    );
                    $paidAmount = (float) $payments->sum(
                        fn($payment) => $payment->paid_amount ?? $payment->booking_amount ?? 0
                    );
                    $holdAmount = 0;
                    $firstPayment = $payments->sortBy('id')->first();
                    $bookingPayment = $payments
                        ->where('transaction_category', 'booking_fee')->sortBy('id')->first();
                    $emiInstallments = $payments
                        ->where('transaction_category', 'emi_payment')
                        ->groupBy(fn($payment) => $payment->receipt_number ?: 'payment-' . $payment->id)
                        ->map(fn($group) => $group->sortBy('id')->first())->values();

                    $ledgerData = (object) [
                        'booking' => $booking,
                        'customer_name' => $booking->primaryDetail?->name ?? '-',
                        'customer_id' => $booking->customer_code ?? '-',
                        'associate_name' => $booking->associate?->associate_name ?? '-',
                        'project_name' => $plotSales->pluck('project.name')->filter()->unique()->implode(', ') ?: '-',
                        'block_name' => $plotSales->pluck('block.block')->filter()->unique()->implode(', ') ?: '-',
                        'plot_no' => $plotSales->pluck('plotDetail.plot_number')->filter()->implode(', ') ?: '-',
                        'plot_amount' => $totalPlotCost,
                        'plots' => $plotSales,
                        'payments' => $payments,
                        'receipt_groups' => $receiptGroups,
                        'first_payment' => $firstPayment,
                        'booking_payment' => $bookingPayment,
                        'paid_amount' => $paidAmount,
                        'hold_amount' => $holdAmount,
                        'due_amount' => max(0, $totalPlotCost - $paidAmount),
                        'emi_installments' => $emiInstallments,
                    ];
                }
            }
        }

        return view('associate-panel.customer-ledger.index', compact('projects', 'blocks', 'plots', 'ledgerData'));
    }

    private function groupPaymentsByReceipt(Collection $payments): Collection
    {
        return $payments
            ->groupBy(fn($payment) => $payment->receipt_number ?: 'payment-' . $payment->id)
            ->map(function (Collection $group) {
                $first = $group->sortByDesc('id')->first();
                $plots = $group->pluck('plotSaleDetail.plotDetail.plot_number')->filter()->unique()->implode(', ');
                $statuses = $group->pluck('payment_status')->filter()->unique()->values();
                $categories = $group->pluck('transaction_category')->filter()->unique()->values();

                return (object) [
                    'id' => $first->id,
                    'receipt_number' => $first->receipt_number ?? '-',
                    'manual_receipt_number' => $first->manual_receipt_number,
                    'plots' => $plots ?: '-',
                    'payment_type' => $categories->count() > 1 ? 'mixed' : ($categories->first() ?? '-'),
                    'paid_amount' => (float) $group->sum(fn($payment) => $payment->paid_amount ?? $payment->booking_amount ?? 0),
                    'due_amount' => (float) ($group->last()?->due_amount ?? 0),
                    'payment_mode' => $first->payment_mode,
                    'payment_status' => $statuses->count() > 1 ? 'mixed' : ($statuses->first() ?? '-'),
                    'created_at' => $first->created_at,
                    'payments' => $group->values(),
                ];
            })
            ->sortByDesc('created_at')
            ->values();
    }

    private function teamAssociateIds(): array
    {
        $associate = auth()->guard('associate')->user() ?: auth()->user();
        if (!$associate) {
            return [];
        }

        return collect(method_exists($associate, 'getDownlineIds') ? $associate->getDownlineIds() : [])
            ->push($associate->id)->unique()->values()->all();
    }
}