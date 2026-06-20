<?php

namespace App\Services;

use App\Models\CustomerPayment;

class UpdateEmiDateService
{
    public function getEmiPayments()
    {
        return CustomerPayment::with([
            'customerBooking.primaryDetail',
            'plotSaleDetail.project',
            'plotSaleDetail.block',
            'plotSaleDetail.plotDetail',
        ])
            ->where('plan_type', 'emi_plan')
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
