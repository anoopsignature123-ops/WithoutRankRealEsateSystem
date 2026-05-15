<?php

namespace App\Services;

use App\Models\CustomerPayment;
use Illuminate\Support\Facades\DB;

class EmiPaymentService
{
    public function store(array $data)
    {
        return DB::transaction(
            function () use ($data) {
                $lastId = CustomerPayment::max('id') + 1;
                $receiptNumber = 'RCP-'.date('Ymd').'-'.str_pad($lastId, 4, '0', STR_PAD_LEFT);
                $data['receipt_number'] = $receiptNumber;
                $data['plan_type'] = 'emi_plan';
                if (in_array($data['payment_mode'], ['cheque', 'dd'])) {
                    $data['payment_status'] = 'hold';
                } else {
                    $data['payment_status'] = 'booked';
                }
                $data['bank_name'] = $data['bank_name'] ?? null;
                $data['account_number'] = $data['account_number'] ?? null;
                $data['branch_name'] = $data['branch_name'] ?? null;
                $data['cheque_number'] = $data['cheque_number'] ?? null;
                $data['cheque_date'] = $data['cheque_date'] ?? null;
                $data['dd_number'] = $data['dd_number'] ?? null;
                $data['transaction_number'] = $data['transaction_number'] ?? null;
                $data['remark'] = $data['remark'] ?? null;

                return CustomerPayment::create($data);
            }
        );
    }
}
