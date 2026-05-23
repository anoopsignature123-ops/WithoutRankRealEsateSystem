<?php

namespace App\Services;

use App\Models\CustomerPayment;

class ChequeClearanceService
{
    public function store(array $data)
    {
        $paymentIds = explode(',', $data['payment_ids']);
        CustomerPayment::whereIn('id', $paymentIds)->update([
            'cheque_status' => $data['cheque_status'],
            'payment_status' => ($data['cheque_status'] === 'cleared') ? 'booked' : 'hold',
            'cheque_reason' => $data['cheque_reason'] ?? null,
            'cheque_date' => $data['cheque_date'],
        ]);

        return true;
    }
}
