<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateEmiDateRequest;
use App\Models\CustomerPayment;
use App\Services\UpdateEmiDateService;

class UpdateEmiDateController extends Controller
{
    protected UpdateEmiDateService $service;

    public function __construct(UpdateEmiDateService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $payments = CustomerPayment::with(['customerBooking.primaryDetail'])
            ->where('plan_type', 'emi_plan')->latest()->get();

        return view('payment.update-emi-date.index', compact('payments'));
    }

    public function store(UpdateEmiDateRequest $request)
    {
        $this->service->store($request->validated());

        return back()->with('success', 'EMI date updated successfully');
    }
}
