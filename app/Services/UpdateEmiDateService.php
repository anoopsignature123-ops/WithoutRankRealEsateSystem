<?php

namespace App\Services;

use App\Models\CustomerPayment;

class UpdateEmiDateService
{
    public function store(array $data)
    {
        $paymentIds = explode(',', $data['payment_ids']);
        CustomerPayment::whereIn('id', $paymentIds)->update([
            'emi_date' => $data['emi_date'],
        ]);

        return true;
    }
}
