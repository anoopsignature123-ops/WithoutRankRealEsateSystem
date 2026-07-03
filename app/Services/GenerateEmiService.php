<?php

namespace App\Services;

use App\Models\CustomerBooking;
use App\Models\CustomerPayment;
use App\Models\PlotSaleDetail;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class GenerateEmiService
{
    public function __construct(
        private EmiPaymentService $emiPaymentService
    ) {}

    public function getCustomers()
    {
        return CustomerBooking::with('primaryDetail')
            ->whereHas('plotSaleDetails', function ($query) {
                $query->whereNotNull('booking_code')
                    ->whereHas('payments', function ($paymentQuery) {
                        $paymentQuery->where('plan_type', 'emi_plan')
                            ->where('booking_status', 'booked');
                    });
            })
            ->orderBy('customer_code')
            ->get();
    }

    public function getList($customerId = null)
    {
        $query = PlotSaleDetail::with([
            'customerBooking.primaryDetail',
            'customerBooking.associate',
            'project',
            'block',
            'plotDetail',
            'payments',
        ])
            ->whereNotNull('booking_code')
            ->whereHas('payments', function ($paymentQuery) {
                $paymentQuery->where('plan_type', 'emi_plan')
                    ->where('booking_status', 'booked');
            });

        if ($customerId) {
            $query->where('customer_booking_id', $customerId);
        }

        return $query->latest()->get()
            ->groupBy(function (PlotSaleDetail $plotSale) {
                return implode('|', [
                    $plotSale->customer_booking_id,
                    $plotSale->booking_code ?: 'plot-'.$plotSale->id,
                ]);
            })
            ->map(function ($group) {
                $first = $group->first();
                $plotSales = $group->values();
                $first->group_plot_sale_ids = $plotSales->pluck('id')->implode(',');
                $first->group_plot_count = $plotSales->count();
                $first->group_projects = $plotSales
                    ->map(fn ($sale) => $sale->project?->name)
                    ->filter()
                    ->unique()
                    ->implode(', ');
                $first->group_blocks = $plotSales
                    ->map(fn ($sale) => $sale->block?->block)
                    ->filter()
                    ->unique()
                    ->implode(', ');
                $first->group_plot_numbers = $plotSales
                    ->map(fn ($sale) => $sale->plotDetail?->plot_number)
                    ->filter()
                    ->unique()
                    ->implode(', ');
                $first->group_total_cost = round((float) $plotSales->sum(fn ($sale) => (float) ($sale->total_plot_cost ?? 0)), 2);
                $first->group_paid = round((float) $plotSales->sum(function ($sale) {
                    return (float) ($sale->payments ?? collect())
                        ->whereIn('payment_status', ['paid', 'cleared'])
                        ->sum('paid_amount');
                }), 2);
                $first->group_due = max(0, round($first->group_total_cost - $first->group_paid, 2));
                $latestPayments = $plotSales
                    ->map(fn ($sale) => ($sale->payments ?? collect())->sortByDesc('id')->first())
                    ->filter();
                $emiMonths = $latestPayments->pluck('emi_months')->filter()->unique()->values();
                $monthlyEmi = $latestPayments->sum(fn ($payment) => (float) ($payment->after_booking_payable_amount ?? 0));
                $holdAmount = round((float) $plotSales->sum(function ($sale) {
                    return (float) ($sale->payments ?? collect())
                        ->where('payment_status', 'hold')
                        ->sum('paid_amount');
                }), 2);
                $emiReceiptGroups = $plotSales
                    ->flatMap(fn ($sale) => ($sale->payments ?? collect())
                        ->where('transaction_category', 'emi_payment'))
                    ->groupBy(fn ($payment) => $payment->receipt_number ?: 'payment-'.$payment->id);
                $paidInstallments = $emiReceiptGroups
                    ->filter(fn ($receiptPayments) => $receiptPayments->whereIn('payment_status', ['paid', 'cleared'])->isNotEmpty())
                    ->count();
                $holdInstallments = $emiReceiptGroups
                    ->filter(function ($receiptPayments) {
                        return $receiptPayments->whereIn('payment_status', ['paid', 'cleared'])->isEmpty()
                            && $receiptPayments->where('payment_status', 'hold')->isNotEmpty();
                    })
                    ->count();
                $remainingInstallments = $this->emiPaymentService->calculateRemainingEmiMonths(
                    (float) $first->group_due,
                    (float) $monthlyEmi
                );
                if ($remainingInstallments <= 0 && $monthlyEmi <= 0) {
                    $remainingInstallments = (int) ($emiMonths->max() ?? 0);
                }
                $totalInstallments = $paidInstallments + $holdInstallments + $remainingInstallments;
                $progressPercent = $totalInstallments > 0
                    ? min(100, round(($paidInstallments / $totalInstallments) * 100))
                    : 0;

                $first->group_current_emi_months = $emiMonths->max();
                $first->group_monthly_emi = round((float) $monthlyEmi, 2);
                $first->group_hold = $holdAmount;
                $first->group_emi_paid_installments = $paidInstallments;
                $first->group_emi_hold_installments = $holdInstallments;
                $first->group_emi_remaining_installments = $remainingInstallments;
                $first->group_emi_total_installments = $totalInstallments;
                $first->group_emi_progress_percent = $progressPercent;
                $first->group_plot_breakdown = $plotSales->map(function (PlotSaleDetail $sale) {
                    $payments = $sale->payments ?? collect();
                    $confirmedPaid = round((float) $payments
                        ->whereIn('payment_status', ['paid', 'cleared'])
                        ->sum('paid_amount'), 2);
                    $holdPaid = round((float) $payments
                        ->where('payment_status', 'hold')
                        ->sum('paid_amount'), 2);
                    $totalCost = round((float) ($sale->total_plot_cost ?? 0), 2);
                    $due = max(0, round($totalCost - $confirmedPaid, 2));
                    $latestPayment = $payments->sortByDesc('id')->first();
                    $monthlyEmi = round((float) ($latestPayment?->after_booking_payable_amount ?? 0), 2);
                    $receiptGroups = $payments
                        ->where('transaction_category', 'emi_payment')
                        ->groupBy(fn ($payment) => $payment->receipt_number ?: 'payment-'.$payment->id);
                    $paidInstallments = $receiptGroups
                        ->filter(fn ($receiptPayments) => $receiptPayments->whereIn('payment_status', ['paid', 'cleared'])->isNotEmpty())
                        ->count();
                    $holdInstallments = $receiptGroups
                        ->filter(function ($receiptPayments) {
                            return $receiptPayments->whereIn('payment_status', ['paid', 'cleared'])->isEmpty()
                                && $receiptPayments->where('payment_status', 'hold')->isNotEmpty();
                        })
                        ->count();
                    $remainingInstallments = $this->emiPaymentService->calculateRemainingEmiMonths($due, $monthlyEmi);
                    $totalInstallments = $paidInstallments + $holdInstallments + $remainingInstallments;
                    $progressPercent = $totalInstallments > 0
                        ? min(100, round(($paidInstallments / $totalInstallments) * 100))
                        : 0;

                    return [
                        'plot' => $sale->plotDetail?->plot_number ?? '-',
                        'project' => $sale->project?->name ?? '-',
                        'block' => $sale->block?->block ?? '-',
                        'area' => number_format((float) ($sale->plot_area ?? 0), 2),
                        'total_cost' => number_format($totalCost, 2),
                        'paid' => number_format($confirmedPaid, 2),
                        'hold' => number_format($holdPaid, 2),
                        'due' => number_format($due, 2),
                        'monthly_emi' => number_format($monthlyEmi, 2),
                        'total_installments' => $totalInstallments,
                        'paid_installments' => $paidInstallments,
                        'hold_installments' => $holdInstallments,
                        'remaining_installments' => $remainingInstallments,
                        'progress_percent' => $progressPercent,
                    ];
                })->values();
                $first->group_emi_schedule = $this->buildEmiSchedule(
                    $plotSales,
                    $remainingInstallments,
                    round((float) $monthlyEmi, 2)
                );
                $first->group_is_emi_generated = $latestPayments->isNotEmpty()
                    && $latestPayments->every(fn ($payment) => (int) ($payment->emi_months ?? 0) > 0 && (float) ($payment->after_booking_payable_amount ?? 0) > 0);
                $first->group_can_generate = $first->group_due > 0 && $latestPayments->count() === $plotSales->count();

                return $first;
            })
            ->sortByDesc('id')
            ->values();
    }

    public function generate($plotSaleDetailId, array $data)
    {
        $plotSale = PlotSaleDetail::with([
            'customerBooking',
            'payments',
        ])->find($plotSaleDetailId);

        if (! $plotSale) {
            throw ValidationException::withMessages([
                'emi_months' => 'Selected plot booking was not found. Please refresh and try again.',
            ]);
        }

        $booking = $plotSale->customerBooking;

        if (! $booking) {
            throw ValidationException::withMessages([
                'emi_months' => 'Customer booking not found for this plot.',
            ]);
        }

        $plotSales = PlotSaleDetail::with('payments')
            ->where('customer_booking_id', $booking->id)
            ->when($plotSale->booking_code, function ($query) use ($plotSale) {
                $query->where('booking_code', $plotSale->booking_code);
            }, function ($query) use ($plotSale) {
                $query->where('id', $plotSale->id);
            })
            ->whereHas('payments', function ($paymentQuery) {
                $paymentQuery->where('plan_type', 'emi_plan');
            })
            ->get();

        if ($plotSales->isEmpty()) {
            throw ValidationException::withMessages([
                'emi_months' => 'EMI plot booking was not found. Please refresh and try again.',
            ]);
        }

        $totalDueAmount = round((float) $plotSales->sum(function (PlotSaleDetail $sale) {
            $totalPlotCost = (float) ($sale->total_plot_cost ?? 0);
            $totalPaid = (float) $sale->payments()
                ->whereIn('payment_status', ['paid', 'cleared'])
                ->sum('paid_amount');

            return max(0, $totalPlotCost - $totalPaid);
        }), 2);

        if ($totalDueAmount <= 0) {
            throw ValidationException::withMessages([
                'emi_months' => 'No due amount available for EMI generation.',
            ]);
        }

        $emiMonths = (int) ($data['emi_months'] ?? 0);

        if ($emiMonths <= 0) {
            throw ValidationException::withMessages([
                'emi_months' => 'EMI months must be greater than zero.',
            ]);
        }

        foreach ($plotSales as $sale) {
            $totalPlotCost = (float) ($sale->total_plot_cost ?? 0);
            $totalPaid = (float) $sale->payments()
                ->whereIn('payment_status', ['paid', 'cleared'])
                ->sum('paid_amount');
            $dueAmount = round(max(0, $totalPlotCost - $totalPaid), 2);

            if ($dueAmount <= 0) {
                continue;
            }

            $latestPayment = CustomerPayment::where('customer_booking_id', $booking->id)
                ->where('plot_sale_detail_id', $sale->id)
                ->latest()
                ->first();

            if (! $latestPayment) {
                throw ValidationException::withMessages([
                    'emi_months' => 'Payment record not found for this booking. Please collect booking payment first.',
                ]);
            }

            if ($latestPayment->plan_type !== 'emi_plan') {
                throw ValidationException::withMessages([
                    'emi_months' => 'EMI can be generated only for EMI plan bookings.',
                ]);
            }

            $latestPayment->update([
                'plan_type' => 'emi_plan',
                'emi_months' => $emiMonths,
                'after_booking_payable_amount' => round($dueAmount / $emiMonths, 2),
                'due_amount' => $dueAmount,
                'net_payable_amount' => $dueAmount,
                'payment_status' => $latestPayment->booking_status === 'hold' ? 'hold' : 'paid',
            ]);
        }

        return true;
    }

    private function buildEmiSchedule($plotSales, int $remainingInstallments, float $monthlyEmi)
    {
        $allPayments = $plotSales
            ->flatMap(fn ($sale) => $sale->payments ?? collect())
            ->sortBy('id')
            ->values();
        $startPayment = $allPayments
            ->where('plan_type', 'emi_plan')
            ->where('transaction_category', 'booking_fee')
            ->first()
            ?: $allPayments->where('plan_type', 'emi_plan')->first();
        $startDate = Carbon::parse($startPayment?->emi_date ?? $startPayment?->created_at ?? now())->startOfDay();
        $receiptGroups = $allPayments
            ->where('transaction_category', 'emi_payment')
            ->groupBy(fn ($payment) => $payment->receipt_number ?: 'payment-'.$payment->id)
            ->sortBy(fn ($payments) => $payments->first()?->created_at ?? now())
            ->values();
        $schedule = collect();
        $installmentNo = 1;

        foreach ($receiptGroups as $receiptPayments) {
            $firstPayment = $receiptPayments->first();
            $statuses = $receiptPayments->pluck('payment_status')->filter()->unique();
            $status = $receiptPayments->whereIn('payment_status', ['paid', 'cleared'])->isNotEmpty()
                ? 'Paid'
                : ($receiptPayments->where('payment_status', 'hold')->isNotEmpty() ? 'Hold' : 'Pending');
            $plotNumbers = $receiptPayments
                ->map(fn ($payment) => $payment->plotSaleDetail?->plotDetail?->plot_number)
                ->filter()
                ->unique()
                ->implode(', ');

            $schedule->push([
                'installment_no' => $installmentNo,
                'due_date' => $startDate->copy()->addMonthsNoOverflow($installmentNo - 1)->format('d-M-Y'),
                'paid_date' => $firstPayment?->created_at?->format('d-M-Y') ?? '-',
                'receipt_no' => $firstPayment?->receipt_number ?? '-',
                'amount' => number_format((float) $receiptPayments->sum('paid_amount'), 2),
                'mode' => strtoupper(str_replace('_', ' / ', (string) ($firstPayment?->payment_mode ?? '-'))),
                'status' => $status,
                'raw_status' => strtolower($status),
                'plots' => $plotNumbers ?: '-',
                'status_detail' => $statuses->implode(', ') ?: '-',
            ]);

            $installmentNo++;
        }

        $remainingDue = round((float) $plotSales->sum(function (PlotSaleDetail $sale) {
            $latestPayment = ($sale->payments ?? collect())->sortByDesc('id')->first();

            return (float) ($latestPayment?->due_amount ?? 0);
        }), 2);

        for ($index = 0; $index < $remainingInstallments; $index++) {
            $pendingAmount = $monthlyEmi > 0
                ? min($monthlyEmi, max(0, $remainingDue - ($index * $monthlyEmi)))
                : 0;

            $schedule->push([
                'installment_no' => $installmentNo,
                'due_date' => $startDate->copy()->addMonthsNoOverflow($installmentNo - 1)->format('d-M-Y'),
                'paid_date' => '-',
                'receipt_no' => '-',
                'amount' => number_format($pendingAmount, 2),
                'mode' => '-',
                'status' => 'Pending',
                'raw_status' => 'pending',
                'plots' => $plotSales
                    ->map(fn ($sale) => $sale->plotDetail?->plot_number)
                    ->filter()
                    ->unique()
                    ->implode(', ') ?: '-',
                'status_detail' => 'Pending',
            ]);

            $installmentNo++;
        }

        return $schedule->values();
    }
}