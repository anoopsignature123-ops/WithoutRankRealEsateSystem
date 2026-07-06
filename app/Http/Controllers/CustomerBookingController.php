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
        $bookingGroups = $plotSales
            ->whereNotNull('booking_code')
            ->groupBy(fn ($sale) => $sale->booking_code ?: 'plot-'.$sale->id)
            ->map(function ($sales, $code) {
                $first = $sales->first();
                $hasPayment = $sales->flatMap->payments->where('transaction_category', 'booking_fee')->isNotEmpty();

                return [
                    'code' => $code,
                    'first_sale_id' => $first?->id,
                    'plot_count' => $sales->count(),
                    'plot_numbers' => $sales->map(fn ($sale) => $sale->plotDetail?->plot_number)->filter()->implode(', '),
                    'project' => $first?->project?->name ?? '-',
                    'block' => $first?->block?->block ?? '-',
                    'total' => (float) $sales->sum('total_plot_cost'),
                    'has_payment' => $hasPayment,
                ];
            })
            ->values();

        $plotSale = $request->plot_sale_detail_id
            ? $plotSales->firstWhere('id', (int) $request->plot_sale_detail_id)
            : null;

        $activePlotSales = collect();

        if ($plotSale) {
            $activePlotSales = $plotSale->booking_code
                ? $plotSales->where('booking_code', $plotSale->booking_code)->values()
                : collect([$plotSale]);
        }

        if ($step == 4 && ! $request->filled('plot_sale_detail_id')) {
            $plotSale = null;
            $activePlotSales = collect();
        }

        $selectedPlotSales = collect();

        if ($step == 5) {
            if ($plotSale) {
                $selectedPlotSales = collect([$plotSale]);
            } else {
                $unpaidGroup = $plotSales
                    ->filter(fn ($sale) => ! $sale->payments->where('transaction_category', 'booking_fee')->count())
                    ->groupBy(fn ($sale) => $sale->booking_code ?: 'plot-'.$sale->id)
                    ->last();

                $selectedPlotSales = $unpaidGroup ? collect([$unpaidGroup->first()]) : collect();
                $plotSale = $selectedPlotSales->first();
                $activePlotSales = $selectedPlotSales;
            }
        }

        $payment = $selectedPlotSales->flatMap->payments->where('transaction_category', 'booking_fee')->first()
            ?: ($plotSale ? $customer->payments->firstWhere('plot_sale_detail_id', $plotSale->id) : null);

        return view('customer-booking.create',
            compact(
                'customer',
                'step',
                'associates',
                'customers',
                'projects',
                'plotSale',
                'payment',
                'plotSales',
                'activePlotSales',
                'bookingGroups',
                'selectedPlotSales'
            ));
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
            try {
                $plotSale = $this->customerBookingService->storeStepFour($id, $validated);
            } catch (\Throwable $e) {
                return back()
                    ->withInput()
                    ->withErrors(['plot_detail_ids' => $e->getMessage()]);
            }
            $plotSaleId = $plotSale instanceof \Illuminate\Support\Collection
                ? $plotSale->first()?->id
                : $plotSale->id;

            return redirect()->route('customer-booking.edit', [
                $id,
                'step' => 5,
                'plot_sale_detail_id' => $plotSaleId,
            ])->with('success', 'Plot details saved successfully.');
        }
        if ($step == 5) {
            $validated = app(CustomerBookingStepFiveRequest::class)->validated();
            try {
                $this->customerBookingService->storeStepFive($id, $validated);
            } catch (\Throwable $e) {
                return back()
                    ->withInput()
                    ->withErrors(['plot_sale_detail_id' => $e->getMessage()]);
            }

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
