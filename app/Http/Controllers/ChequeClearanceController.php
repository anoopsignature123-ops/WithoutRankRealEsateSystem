<?php

namespace App\Http\Controllers;

use App\Http\Requests\MultipleChequeClearanceRequest;
use App\Models\CustomerPayment;
use App\Services\ChequeClearanceService;

class ChequeClearanceController extends Controller
{
    protected ChequeClearanceService $service;

    public function __construct(ChequeClearanceService $service) {
        $this->service = $service;
    }

    public function multipleChequeClearanceIndex()
    {
        $payments = CustomerPayment::with([
            'customerBooking.primaryDetail',
            'plotSaleDetail.project',
            'plotSaleDetail.block',
            'plotSaleDetail.plotDetail',
        ])->where('payment_mode', 'cheque')->latest()->get();
        return view('payment.multiple-cheque-clearance.index',compact('payments'));
    }

    public function storeMultipleChequeClearance(MultipleChequeClearanceRequest $request) {
        $this->service->store($request->validated());
        return back()->with('success','Cheque status updated successfully');
    }
}
