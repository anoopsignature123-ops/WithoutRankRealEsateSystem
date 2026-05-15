<?php

namespace App\Services;

use App\Models\CustomerPayment;

class ChequeClearanceService
{
    public function store(
        array $data
    ) {
        $paymentIds = explode(
            ',',
            $data['payment_ids']
        );

        CustomerPayment::whereIn(
            'id',
            $paymentIds
        )->update([

            'cheque_status' => $data['cheque_status'],

            'cheque_reason' => $data['cheque_reason'] ?? null,

        ]);

        return true;
    }
}
