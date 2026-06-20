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
        $payments = $this->plotPaymentService->getAll();

        $selectedPayment = null;

        if ($request->filled('selected_payment')) {
            $selectedPayment = $this->plotPaymentService->findPaymentById(
                $request->selected_payment
            );
        }

        return view('plot-payment.index', compact('payments', 'selectedPayment'));
    }

    public function update(PlotPaymentRequest $request, $id)
    {
        $this->plotPaymentService->updatePayment(
            $id,
            $request->validated()
        );

        return redirect()
            ->route('edit-payment-details.index')
            ->with('success', 'Payment updated successfully.');
    }
}
