<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerBookingRequest;
use App\Http\Requests\CustomerBookingStepFiveRequest;
use App\Http\Requests\CustomerBookingStepFourRequest;
use App\Http\Requests\CustomerBookingStepThreeRequest;
use App\Http\Requests\CustomerBookingStepTwoRequest;
use App\Models\CustomerBooking;
use App\Services\CustomerBookingService;
use Illuminate\Http\Request;

class CustomerBookingController extends Controller
{
    protected $customerBookingService;

    public function __construct(CustomerBookingService $customerBookingService)
    {
        $this->customerBookingService = $customerBookingService;
    }

    public function index()
    {
        $customers = $this->customerBookingService->getAll();

        return view('customer-booking.index', compact('customers'));
    }

    public function create()
    {
        $step = 1;
        $associates = $this->customerBookingService->getAssociates();
        $customers = $this->customerBookingService->getCustomers();

        return view('customer-booking.create', compact('step', 'associates', 'customers'));
    }

    public function store(CustomerBookingRequest $request)
    {
        $customer = $this->customerBookingService->storeStepOne($request->validated());

        return redirect()->route('customer-booking.edit', [$customer->id, 'step' => 2]);
    }

    public function findById($id)
    {
        return CustomerBooking::findOrFail($id);
    }

    public function edit(Request $request, $id)
    {
        $customer = $this->customerBookingService->findById($id);
        $step = $request->step ?? $customer->current_step;
        $associates = $this->customerBookingService->getAssociates();
        $customers = $this->customerBookingService->getCustomers();
        $projects = $this->customerBookingService->getProjects();
        $plotSales = $customer->plotSaleDetails;
        $plotSale = $request->plot_sale_detail_id
            ? $plotSales->firstWhere('id', $request->plot_sale_detail_id)
            : $customer->plotSaleDetail;
        $payment = $customer->payments->firstWhere('plot_sale_detail_id', $request->plot_sale_detail_id);

        return view('customer-booking.create',
            compact('customer', 'step', 'associates', 'customers', 'projects', 'plotSale', 'payment', 'plotSales'));
    }

    public function update(Request $request, $id)
    {
        $step = $request->step;
        if ($step == 1) {
            $validated = app(CustomerBookingRequest::class)->validated();
            $customer = $this->customerBookingService->storeStepOne($validated, $id);

            return redirect()
                ->route('customer-booking.edit', [$customer->id, 'step' => 2])
                ->with('success', 'Step 1 updated successfully.');
        }
        if ($request->step == 2) {
            $validated = app(CustomerBookingStepTwoRequest::class)->validated();
            $this->customerBookingService->storeStepTwo($id, $validated);

            return redirect()->route('customer-booking.edit', [$id, 'step' => 3]);
        }

        if ($step == 3) {
            app(CustomerBookingStepThreeRequest::class)->validated();
            $this->customerBookingService->storeStepThree($id, $request);

            return redirect()->route('customer-booking.edit', [$id, 'step' => 4])
                ->with('success', 'Documents uploaded successfully.');
        }
        if ($step == 4) {
            $validated = app(CustomerBookingStepFourRequest::class)->validated();
            $plotSale = $this->customerBookingService->storeStepFour($id, $validated);

            return redirect()->route('customer-booking.edit', [
                $id,
                'step' => 5,
                'plot_sale_detail_id' => $plotSale->id,
            ])->with('success', 'Plot details saved successfully.');
        }
        if ($step == 5) {
            $validated = app(CustomerBookingStepFiveRequest::class)->validated();
            $this->customerBookingService->storeStepFive($id, $validated);

            return redirect()->route('customer-booking.index')
                ->with('success', 'Customer booking completed successfully.');
        }

        return back();
    }

    public function getBlocks($projectId)
    {
        return $this->customerBookingService->getBlocksByProject($projectId);
    }

    public function getPlots($blockId, $customerId = null)
    {
        return $this->customerBookingService->getPlotsByBlock($blockId, $customerId);
    }

    public function destroy($id)
    {
        $this->customerBookingService->deleteBooking($id);

        return redirect()->route('customer-booking.index')->with('success', 'Customer booking deleted successfully.');
    }
}
