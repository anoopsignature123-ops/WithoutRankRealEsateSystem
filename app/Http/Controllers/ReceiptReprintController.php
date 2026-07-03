<?php

namespace App\Http\Controllers;

use App\Models\CustomerBooking;
use App\Services\ReceiptReprintService;
use Illuminate\Http\Request;

class ReceiptReprintController extends Controller
{
    public function __construct(
        protected ReceiptReprintService $service
    ) {}

    public function index()
    {
        $customers = CustomerBooking::with('primaryDetail')
            ->whereHas('payments', function ($query) {
                $query->where('booking_status', 'booked');
            })
            ->orderBy('customer_code')
            ->get();

        return view('payment.receipt-reprint.index', compact('customers'));
    }

    public function search(Request $request)
    {
        $request->validate([
            'customer_booking_id' => 'required|exists:customer_bookings,id',
            'receipt_group' => 'nullable|string',
        ], [
            'customer_booking_id.required' => 'Please select customer.',
        ]);

        $customers = CustomerBooking::with('primaryDetail')
            ->whereHas('payments', function ($query) {
                $query->where('booking_status', 'booked');
            })
            ->orderBy('customer_code')
            ->get();

        $receipts = $this->service->search(
            $request->customer_booking_id,
            $request->receipt_group
        );

        $summary = [
            'count' => $receipts->count(),
            'amount' => (float) $receipts->sum(fn ($receipt) => $receipt->group_amount ?? $receipt->paid_amount ?? $receipt->booking_amount ?? 0),
            'latest' => $receipts->max('created_at'),
        ];

        return view('payment.receipt-reprint.index', compact('customers', 'receipts', 'summary'))
            ->with($request->only(['customer_booking_id', 'receipt_group']));
    }

    public function download($paymentId)
    {
        return $this->service->downloadPdf($paymentId);
    }

    public function getReceiptGroupsByCustomer($customerBookingId)
    {
        CustomerBooking::whereHas('payments', function ($query) {
            $query->where('booking_status', 'booked');
        })->findOrFail($customerBookingId);

        return response()->json($this->service->receiptGroupsByCustomer($customerBookingId));
    }
}
