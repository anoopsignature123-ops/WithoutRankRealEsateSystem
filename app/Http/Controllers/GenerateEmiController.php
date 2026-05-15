<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateEmiRequest;
use App\Services\GenerateEmiService;
use Illuminate\Http\Request;

class GenerateEmiController extends Controller
{
    public function __construct(
        private GenerateEmiService $generateEmiService
    ) {}

    public function index(Request $request)
    {
        $customers = $this->generateEmiService->getCustomers();
        $records = $this->generateEmiService->getList($request->customer_id);
        return view('payment.generate-emi.index', compact('customers', 'records'));
    }

    public function store(GenerateEmiRequest $request, $id)
    {
        $this->generateEmiService->generate($id, $request->validated());
        return back()->with('success', 'EMI generated successfully');
    }
}