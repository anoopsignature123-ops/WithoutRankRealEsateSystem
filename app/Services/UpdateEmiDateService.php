<?php

namespace App\Services;

use App\Models\CustomerPayment;

class UpdateEmiDateService
{
    public function getEmiPayments()
    {
        $latestPayments = CustomerPayment::with([
            'customerBooking.primaryDetail',
            'plotSaleDetail.project',
            'plotSaleDetail.block',
            'plotSaleDetail.plotDetail',
        ])
            ->where('plan_type', 'emi_plan')
            ->where('booking_status', 'booked')
            ->whereNotNull('plot_sale_detail_id')
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('customer_payments')
                    ->where('plan_type', 'emi_plan')
                    ->whereNotNull('plot_sale_detail_id')
                    ->groupBy('customer_booking_id', 'plot_sale_detail_id');
            })
            ->latest()
            ->get();

        return $latestPayments
            ->groupBy(function ($payment) {
                $plotBookingCode = $payment->plotSaleDetail?->booking_code
                    ?: $payment->customerBooking?->booking_code
                    ?: 'booking-'.$payment->customer_booking_id;

                return implode('|', [
                    $payment->customer_booking_id,
                    $plotBookingCode,
                ]);
            })
            ->map(function ($group) {
                $first = $group->first();
                $plotSales = $group->pluck('plotSaleDetail')->filter();
                $emiMonths = $group->pluck('emi_months')->filter()->unique()->values();
                $emiDates = $group
                    ->pluck('emi_date')
                    ->filter()
                    ->map(fn ($date) => $date->format('Y-m-d'))
                    ->unique()
                    ->values();

                $first->group_payment_ids = $group->pluck('id')->implode(',');
                $first->group_plot_count = $plotSales->count();
                $first->group_plot_numbers = $plotSales
                    ->map(fn ($sale) => $sale?->plotDetail?->plot_number)
                    ->filter()
                    ->unique()
                    ->implode(', ');
                $first->group_blocks = $plotSales
                    ->map(fn ($sale) => $sale?->block?->block)
                    ->filter()
                    ->unique()
                    ->implode(', ');
                $first->group_projects = $plotSales
                    ->map(fn ($sale) => $sale?->project?->name)
                    ->filter()
                    ->unique()
                    ->implode(', ');
                $first->group_monthly_emi = $group->sum(fn ($payment) => (float) ($payment->after_booking_payable_amount ?? ($payment->paid_amount ?? 0)));
                $first->group_due_amount = $group->sum(fn ($payment) => (float) ($payment->due_amount ?? 0));
                $first->group_emi_months = ($emiMonths->max() ?? 0).' Months';
                $first->group_has_mixed_emi_months = $emiMonths->count() > 1;
                $first->group_emi_date = $emiDates->count() === 1 ? $emiDates->first() : null;
                $first->group_has_mixed_emi_dates = $emiDates->count() > 1;

                return $first;
            })
            ->sortByDesc('id')
            ->values();
    }

    public function store(array $data)
    {
        $paymentIds = collect(explode(',', $data['payment_ids']))
            ->map(fn ($id) => (int) trim($id))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($paymentIds)) {
            return false;
        }

        CustomerPayment::whereIn('id', $paymentIds)
            ->where('plan_type', 'emi_plan')
            ->update([
                'emi_date' => $data['emi_date'],
            ]);

        return true;
    }
}