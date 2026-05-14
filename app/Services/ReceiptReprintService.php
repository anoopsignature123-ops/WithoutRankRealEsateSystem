<?php

namespace App\Services;

use App\Models\CustomerPayment;
use Barryvdh\DomPDF\Facade\Pdf;

class ReceiptReprintService
{
    public function search(
        $plotId,
        $customerId
    ) {
        return CustomerPayment::with([
            'customerBooking.primaryDetail',
            'customerBooking.plotSaleDetail',
        ])
            ->whereHas(
                'customerBooking',
                function ($query) use (
                    $plotId,
                    $customerId
                ) {

                    $query
                        ->where(
                            'customer_code',
                            $customerId
                        )

                        ->whereHas(
                            'plotSaleDetail',
                            function ($q) use ($plotId) {

                                $q->where(
                                    'plot_detail_id',
                                    $plotId
                                );
                            }
                        );
                }
            )
            ->get();
    }

    public function downloadPdf(
        $paymentId
    ) {
        $payment = CustomerPayment::with([
            'customerBooking.primaryDetail',
        ])
            ->findOrFail(
                $paymentId
            );

        $pdf = Pdf::loadView(
            'payment.receipt-reprint.pdf',
            compact('payment')
        );

        return $pdf->download(
            'receipt-'.$payment->receipt_number.'.pdf'
        );
    }
}
