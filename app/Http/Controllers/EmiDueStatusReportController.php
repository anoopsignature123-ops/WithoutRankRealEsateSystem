<?php

namespace App\Http\Controllers;

use App\Models\CustomerPayment;
use App\Services\ExcelExportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class EmiDueStatusReportController extends Controller
{
    protected $excelExportService;

    public function __construct(ExcelExportService $excelExportService)
    {
        $this->excelExportService = $excelExportService;
    }

    public function index(Request $request)
    {
        $asOfDate = $this->resolveAsOfDate($request);
        $reports = $this->buildReports($request, $asOfDate);
        $summary = $this->buildSummary($reports);

        return view('reports.emi_due_status_report.index', compact('reports', 'summary', 'asOfDate'));
    }

    public function export(Request $request)
    {
        $asOfDate = $this->resolveAsOfDate($request);
        $reports = $this->buildReports($request, $asOfDate);

        return $this->excelExportService->export($reports, 'emi-due-status-report',
            [
                'Agent ID',
                'Customer ID',
                'Booking ID',
                'Customer Name',
                'Mobile',
                'Project',
                'Block',
                'Plot No',
                'Monthly EMI',
                'Total Due Amount',
                'Paid EMI',
                'Hold EMI',
                'Due Till Date',
                'Pending EMI',
                'Next Due Date',
                'Status',
            ],
            function ($report) {
                return [
                    $report['agent_id'],
                    $report['customer_id'],
                    $report['booking_id'],
                    $report['customer_name'],
                    $report['mobile'],
                    $report['project'],
                    $report['block'],
                    $report['plots'],
                    number_format($report['monthly_emi'], 2, '.', ''),
                    number_format($report['total_due_amount'], 2, '.', ''),
                    $report['paid_installments'],
                    $report['hold_installments'],
                    $report['due_till_date'],
                    $report['pending_installments'],
                    $report['next_due_date'] ? $report['next_due_date']->format('d-m-Y') : 'N/A',
                    $report['status_label'],
                ];
            }
        );
    }

    private function buildReports(Request $request, Carbon $asOfDate): Collection
    {
        $basePayments = CustomerPayment::with([
            'customerBooking.primaryDetail.correspondenceDetail',
            'plotSaleDetail.project',
            'plotSaleDetail.block',
            'plotSaleDetail.plotDetail',
        ])
            ->where('plan_type', 'emi_plan')
            ->where('booking_status', 'booked')
            ->where('transaction_category', 'booking_fee')
            ->whereNotNull('plot_sale_detail_id')
            ->when($request->filled('customer_name'), function ($query) use ($request) {
                $query->whereHas('customerBooking.primaryDetail', function ($subQuery) use ($request) {
                    $subQuery->where('name', 'like', '%' . $request->customer_name . '%');
                });
            })
            ->when($request->filled('mobile'), function ($query) use ($request) {
                $query->whereHas('customerBooking.primaryDetail.correspondenceDetail', function ($subQuery) use ($request) {
                    $subQuery->where('mobile_number', 'like', '%' . $request->mobile . '%');
                });
            })
            ->latest()
            ->get();

        return $basePayments
            ->groupBy(function ($payment) {
                $bookingCode = $payment->plotSaleDetail?->booking_code ?: 'plot-' . $payment->plot_sale_detail_id;

                return $payment->customer_booking_id . '|' . $bookingCode;
            })
            ->map(fn ($payments) => $this->buildReportRow($payments, $asOfDate))
            ->filter()
            ->when($request->filled('due_date'), function ($reports) use ($request) {
                $dueDate = Carbon::parse($request->due_date)->toDateString();

                return $reports->filter(fn ($report) => $report['next_due_date']?->toDateString() === $dueDate);
            })
            ->when($request->filled('status'), function ($reports) use ($request) {
                return $reports->filter(fn ($report) => $report['status'] === $request->status);
            })
            ->sortByDesc(fn ($report) => $report['pending_installments'])
            ->values();
    }

    private function buildReportRow(Collection $basePayments, Carbon $asOfDate): ?array
    {
        $firstPayment = $basePayments->sortBy('id')->first();
        $booking = $firstPayment?->customerBooking;

        if (! $firstPayment || ! $booking) {
            return null;
        }

        $plotSaleIds = $basePayments->pluck('plot_sale_detail_id')->filter()->unique()->values();

        if ($plotSaleIds->isEmpty()) {
            return null;
        }

        $allPayments = CustomerPayment::with([
            'plotSaleDetail.project',
            'plotSaleDetail.block',
            'plotSaleDetail.plotDetail',
        ])
            ->where('customer_booking_id', $firstPayment->customer_booking_id)
            ->whereIn('plot_sale_detail_id', $plotSaleIds)
            ->where('plan_type', 'emi_plan')
            ->where('booking_status', 'booked')
            ->orderBy('id')
            ->get();

        $latestByPlot = $allPayments->groupBy('plot_sale_detail_id')->map->last();
        $plotSales = $basePayments->pluck('plotSaleDetail')->filter()->unique('id')->values();
        $startDate = Carbon::parse($firstPayment->emi_date ?? $firstPayment->created_at ?? now())->startOfDay();
        $paidReceiptGroups = $allPayments
            ->where('transaction_category', 'emi_payment')
            ->groupBy(fn ($payment) => $payment->receipt_number ?: 'payment-' . $payment->id);
        $paidInstallments = $paidReceiptGroups
            ->filter(fn ($receiptPayments) => $receiptPayments->whereIn('payment_status', ['paid', 'cleared'])->isNotEmpty())
            ->count();
        $holdInstallments = $paidReceiptGroups
            ->filter(function ($receiptPayments) {
                return $receiptPayments->whereIn('payment_status', ['paid', 'cleared'])->isEmpty()
                    && $receiptPayments->where('payment_status', 'hold')->isNotEmpty();
            })
            ->count();
        $monthlyEmi = round((float) $latestByPlot->sum(fn ($payment) => (float) ($payment->after_booking_payable_amount ?? 0)), 2);
        $totalDueAmount = round((float) $latestByPlot->sum(fn ($payment) => (float) ($payment->due_amount ?? 0)), 2);
        $remainingInstallments = $this->calculateRemainingInstallments($totalDueAmount, $monthlyEmi);
        $totalInstallments = max(
            (int) $basePayments->max('emi_months'),
            $paidInstallments + $holdInstallments + $remainingInstallments
        );
        $dueTillDate = $this->calculateDueTillDate($startDate, $asOfDate, $totalInstallments);
        $pendingInstallments = max(0, $dueTillDate - $paidInstallments - $holdInstallments);
        $nextDueIndex = min($paidInstallments + $holdInstallments + 1, max($totalInstallments, 1));
        $nextDueDate = $totalDueAmount > 0 ? $startDate->copy()->addMonthsNoOverflow($nextDueIndex - 1) : null;
        $status = $this->resolveStatus($totalDueAmount, $pendingInstallments, $holdInstallments, $nextDueDate, $asOfDate);

        return [
            'agent_id' => $booking->associate_code ?? 'N/A',
            'customer_id' => $booking->customer_code ?? 'N/A',
            'booking_id' => $plotSales->first()?->booking_code ?? $booking->booking_code ?? 'N/A',
            'customer_name' => $booking->primaryDetail?->name ?? 'N/A',
            'mobile' => $booking->primaryDetail?->correspondenceDetail?->mobile_number ?? 'N/A',
            'project' => $plotSales->map(fn ($sale) => $sale->project?->name)->filter()->unique()->implode(', ') ?: 'N/A',
            'block' => $plotSales->map(fn ($sale) => $sale->block?->block)->filter()->unique()->implode(', ') ?: 'N/A',
            'plots' => $plotSales->map(fn ($sale) => $sale->plotDetail?->plot_number)->filter()->unique()->implode(', ') ?: 'N/A',
            'plot_count' => $plotSales->count(),
            'monthly_emi' => $monthlyEmi,
            'total_due_amount' => $totalDueAmount,
            'paid_amount' => round((float) $allPayments->whereIn('payment_status', ['paid', 'cleared'])->sum('paid_amount'), 2),
            'hold_amount' => round((float) $allPayments->where('payment_status', 'hold')->sum('paid_amount'), 2),
            'paid_installments' => $paidInstallments,
            'hold_installments' => $holdInstallments,
            'remaining_installments' => $remainingInstallments,
            'total_installments' => $totalInstallments,
            'due_till_date' => $dueTillDate,
            'pending_installments' => $pendingInstallments,
            'start_date' => $startDate,
            'next_due_date' => $nextDueDate,
            'status' => $status,
            'status_label' => ucwords(str_replace('_', ' ', $status)),
        ];
    }

    private function buildSummary(Collection $reports): array
    {
        return [
            'total_records' => $reports->count(),
            'due_records' => $reports->where('status', 'due')->count(),
            'overdue_records' => $reports->where('status', 'overdue')->count(),
            'hold_records' => $reports->where('status', 'hold')->count(),
            'completed_records' => $reports->where('status', 'completed')->count(),
            'pending_installments' => $reports->sum('pending_installments'),
            'total_due_amount' => $reports->sum('total_due_amount'),
        ];
    }

    private function resolveAsOfDate(Request $request): Carbon
    {
        return $request->filled('as_of_date')
            ? Carbon::parse($request->as_of_date)->endOfDay()
            : now()->endOfDay();
    }

    private function calculateRemainingInstallments(float $dueAmount, float $monthlyEmi): int
    {
        if ($dueAmount <= 0 || $monthlyEmi <= 0) {
            return 0;
        }

        $months = $dueAmount / $monthlyEmi;
        $roundedMonths = (int) round($months);

        if ($roundedMonths > 0 && abs(($monthlyEmi * $roundedMonths) - $dueAmount) <= 0.05) {
            return $roundedMonths;
        }

        return (int) ceil($months);
    }

    private function calculateDueTillDate(Carbon $startDate, Carbon $asOfDate, int $totalInstallments): int
    {
        if ($totalInstallments <= 0 || $asOfDate->lt($startDate)) {
            return 0;
        }

        return min($totalInstallments, $startDate->diffInMonths($asOfDate) + 1);
    }

    private function resolveStatus(float $dueAmount, int $pendingInstallments, int $holdInstallments, ?Carbon $nextDueDate, Carbon $asOfDate): string
    {
        if ($dueAmount <= 0) {
            return 'completed';
        }

        if ($pendingInstallments > 1) {
            return 'overdue';
        }

        if ($pendingInstallments === 1) {
            return 'due';
        }

        if ($holdInstallments > 0) {
            return 'hold';
        }

        if ($nextDueDate && $nextDueDate->gt($asOfDate)) {
            return 'upcoming';
        }

        return 'due';
    }
}