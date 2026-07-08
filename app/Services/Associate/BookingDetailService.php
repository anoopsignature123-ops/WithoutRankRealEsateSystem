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
            ->where('booking_status', 'booked')
            ->whereIn('payment_status', ['paid', 'cleared'])
            ->whereHas('plotSaleDetail', function ($q) {
                $q->where('status', 'active');
            })
            ->whereHas('booking', fn($q) => $q->whereIn('associate_id', $associateIds))
            ->when($request->project_id, fn($q) => $q->whereHas('plotSaleDetail', fn($sub) => $sub->where('project_id', $request->project_id)))
            ->when($request->block_id, fn($q) => $q->whereHas('plotSaleDetail', fn($sub) => $sub->where('block_id', $request->block_id)))
            ->when($request->plot_id, fn($q) => $q->where('plot_sale_detail_id', $request->plot_id))
            ->when($request->customer_id, fn($q) => $q->whereHas('booking', fn($sub) => $sub->where('customer_code', $request->customer_id)))
            ->when($request->booking_id, fn($q) => $q->whereHas('booking', fn($sub) => $sub->where('booking_code', $request->booking_id)))
            ->when($request->from_date, fn($q) => $q->whereDate('created_at', '>=', $request->from_date))
            ->when($request->to_date, fn($q) => $q->whereDate('created_at', '<=', $request->to_date))
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
        $associateIds = $this->teamAssociateIds();

        $plotIds = CustomerPayment::where('booking_status', 'booked')
            ->whereIn('payment_status', ['paid', 'cleared'])
            ->whereHas('booking', function ($q) use ($associateIds) {
                $q->whereIn('associate_id', $associateIds);
            })
            ->whereHas('plotSaleDetail', function ($q) use ($blockId) {
                $q->where('block_id', $blockId)
                    ->where('status', 'active');
            })->with('plotSaleDetail')->get()->pluck('plotSaleDetail.plot_detail_id')->filter()->unique()->values();
        return PlotDetail::whereIn('id', $plotIds)->where('block_id', $blockId)->get();
    }

    public function getBookingDataByPlot($plotId)
    {
        $associateIds = $this->teamAssociateIds();

        $payment = CustomerPayment::with('booking')
            ->where('booking_status', 'booked')
            ->whereIn('payment_status', ['paid', 'cleared'])
            ->whereHas('booking', function ($q) use ($associateIds) {
                $q->whereIn('associate_id', $associateIds);
            })
            ->whereHas('plotSaleDetail', function ($q) use ($plotId) {
                $q->where('plot_detail_id', $plotId)
                    ->where('status', 'active');
            })->latest()->first();

        $booking = $payment?->booking;
        return $booking ? ['customer_id' => $booking->customer_code, 'booking_id' => $booking->booking_code] : [];
    }

    private function groupPaymentRecords(Collection $payments): Collection
    {
        return $payments
            ->groupBy(fn($payment) => $payment->receipt_number ?: 'payment-' . $payment->id)
            ->map(function (Collection $group) {
                $first = $group->sortByDesc('id')->first();
                $booking = $first->booking;

                $plots = $group->pluck('plotSaleDetail')
                    ->filter(fn($plotSale) => $plotSale && $plotSale->status === 'active')->unique('id')->values();
                $amount = (float) $group->sum(fn($payment) => $payment->paid_amount ?? $payment->booking_amount ?? 0);
                $payable = (float) $plots->sum(
                    fn($plotSale) => $plotSale->total_plot_cost ?? $plotSale->final_payable ?? $plotSale->plot_cost ?? 0
                );
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
            ->filter(fn($item) => $item->plot_count > 0)->sortByDesc('created_at')->values();
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
            'plotSaleDetails.payments',
        ])
            ->whereIn('associate_id', $associateIds)
            ->whereHas('plotSaleDetails', function ($q) {
                $q->where('status', 'active')
                    ->whereHas('payments', function ($p) {
                        $p->where('booking_status', 'booked')
                            ->whereIn('payment_status', ['paid', 'cleared']);
                    });
            })
            ->latest()
            ->get()
            ->flatMap(function ($booking) {
                return $booking->plotSaleDetails
                    ->filter(function ($plotSale) {
                        return $plotSale->status === 'active'
                            && $plotSale->payments->contains(function ($payment) {
                                return $payment->booking_status === 'booked'
                                    && in_array($payment->payment_status, ['paid', 'cleared']);
                            });
                    })
                    ->map(function ($plotSale) use ($booking) {
                        return (object) [
                            'booking_code' => $plotSale->booking_code ?? $booking->booking_code ?? '-',
                            'customer_name' => $booking->primaryDetail?->name ?? '-',
                            'agent_name' => $booking->associate?->associate_name ?? '-',
                            'project_name' => $plotSale->project?->name ?? '-',
                            'plot_no' => $plotSale->plotDetail?->plot_number ?? '-',
                            'amount' => (float) (
                                $plotSale->total_plot_cost
                                ?? $plotSale->final_payable
                                ?? $plotSale->plot_cost
                                ?? 0
                            ),
                            'date' => $plotSale->booking_date
                                ? \Carbon\Carbon::parse($plotSale->booking_date)->format('d-m-Y')
                                : $booking->created_at?->format('d-m-Y'),
                        ];
                    });
            })
            ->filter()
            ->values();
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
            ->whereHas('plotSaleDetails', function ($q) {
                $q->where('status', 'active')
                    ->whereHas('payments', function ($p) {
                        $p->where('booking_status', 'booked')
                            ->whereIn('payment_status', ['paid', 'cleared'])
                            ->where('plan_type', 'emi_plan');
                    });
            })
            ->latest()
            ->get()
            ->flatMap(function ($booking) {
                return $booking->plotSaleDetails
                    ->filter(function ($plotSale) {
                        return $plotSale->status === 'active'
                            && $plotSale->payments->contains(function ($payment) {
                                return $payment->plan_type === 'emi_plan'
                                    && $payment->booking_status === 'booked'
                                    && in_array($payment->payment_status, ['paid', 'cleared']);
                            });
                    })
                    ->map(function ($plotSale) use ($booking) {
                        $allPayments = $booking->payments
                            ->where('plot_sale_detail_id', $plotSale->id)
                            ->where('plan_type', 'emi_plan')
                            ->sortBy('id')
                            ->values();

                        $bookingPayment = $allPayments
                            ->where('transaction_category', 'booking_fee')
                            ->where('booking_status', 'booked')
                            ->whereIn('payment_status', ['paid', 'cleared'])
                            ->sortBy('id')
                            ->first();

                        if (!$bookingPayment) {
                            return null;
                        }

                        $emiPayments = $allPayments
                            ->where('transaction_category', 'emi_payment')
                            ->where('booking_status', 'booked')
                            ->whereIn('payment_status', ['paid', 'cleared']);

                        $emiInstallments = $emiPayments
                            ->groupBy(fn($payment) => $payment->receipt_number ?: 'payment-' . $payment->id)
                            ->map(fn($group) => $group->sortBy('id')->first())
                            ->values();

                        $latestPayment = $allPayments
                            ->where('booking_status', 'booked')
                            ->whereIn('payment_status', ['paid', 'cleared'])
                            ->last();

                        $totalInstallments = (int) ($bookingPayment?->emi_months ?? 0);
                        $currentDueAmount = (float) ($latestPayment?->due_amount ?? 0);
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
                            'booking_code' => $plotSale->booking_code ?? $booking->booking_code ?? '-',
                            'customer_name' => $booking->primaryDetail?->name ?? '-',
                            'associate_name' => $booking->associate?->associate_name ?? '-',
                            'project_name' => $plotSale->project?->name ?? '-',
                            'block_name' => $plotSale->block?->block ?? '-',
                            'plot_no' => $plotSale->plotDetail?->plot_number ?? '-',
                            'plot_amount' => round((float) (
                                $plotSale->total_plot_cost
                                ?? $plotSale->final_payable
                                ?? $plotSale->plot_cost
                                ?? 0
                            ), 2),
                            'booking_amount' => round((float) ($bookingPayment?->booking_amount ?? 0), 2),
                            'due_amount' => round($currentDueAmount, 2),
                            'emi_amount' => round($monthlyEmi, 2),
                            'total_installments' => $totalInstallments,
                            'paid_installments' => $paidInstallments,
                            'remaining_installments' => $remainingInstallments,
                            'emi_progress' => "{$paidInstallments}/{$totalInstallments}",
                            'progress_percent' => $totalInstallments > 0
                                ? round(($paidInstallments / $totalInstallments) * 100, 2)
                                : 0,
                            'status' => $remainingInstallments > 0 ? 'Pending' : 'Completed',
                            'emi_history' => $emiHistory,
                        ];
                    });
            })
            ->filter()
            ->values();
    }

    private function teamAssociateIds(): array
    {
        $associate = auth()->guard('associate')->user() ?: auth()->user();

        if (!$associate) {
            return [];
        }

        return collect(method_exists($associate, 'getDownlineIds') ? $associate->getDownlineIds() : [])
            ->push($associate->id)
            ->unique()
            ->values()
            ->all();
    }
}