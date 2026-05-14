<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlotPaymentRequest;
use App\Services\PlotPaymentService;
use Illuminate\Http\Request;

class PlotPaymentController extends Controller
{
    public function __construct(
        private PlotPaymentService $plotPaymentService
    ) {}

    public function index(Request $request)
    {
        $bookings = $this->plotPaymentService->getAll();
        $selectedBooking = null;

        if ($request->filled('selected_booking')) {
            $selectedBooking = $this->plotPaymentService->findById(
                $request->input('selected_booking')
            );
        }

        return view('plot-payment.index', compact('bookings', 'selectedBooking'));
    }

    public function edit($id)
    {
        $booking = $this->plotPaymentService->findById($id);
        $plotSale = $booking->plotSaleDetail;
        $payment = $booking->payment;

        return view('plot-payment.edit', compact('booking', 'plotSale', 'payment'));
    }

    public function update(PlotPaymentRequest $request, $id)
    {
        $this->plotPaymentService->savePayment(
            $id,
            $request->validated()
        );

        return redirect()
            ->route('admin.edit-payment-details.index')
            ->with('success', 'Plot payment details updated successfully.');
    }
}
