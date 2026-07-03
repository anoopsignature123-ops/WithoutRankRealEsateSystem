<?php

namespace App\Http\Controllers;

use App\Models\CustomerBooking;
use App\Services\ExcelExportService;
use Illuminate\Http\Request;

class CustomerDetailReportController extends Controller
{
    protected $excelExportService;

    public function __construct(ExcelExportService $excelExportService)
    {
        $this->excelExportService = $excelExportService;
    }

    public function index(Request $request)
    {
        $customers = $this->buildCustomers($request);

        $summary = [
            'total_customers' => $customers->count(),
            'total_plots' => $customers->sum('total_bookings'),
            'self_customers' => $customers->filter(fn($item) => empty($item->parentCustomer))->count(),
            'reference_customers' => $customers->filter(fn($item) => !empty($item->parentCustomer))->count(),
        ];

        return view('reports.customer-detail.index', compact('customers', 'summary'));
    }

    public function export(Request $request)
    {
        $customers = $this->buildCustomers($request);

        return $this->excelExportService->export(
            $customers,
            'customer-detail-report',
            [
                'Customer ID',
                'Reference Customer',
                'Customer Name',
                'Address',
                'Mobile',
                'Email',
                'Total Plots',
                'Created Date',
            ],
            function ($customer) {
                $primary = $customer->primaryDetail;
                $contact = $primary?->correspondenceDetail;

                $address = $primary?->permanent_address
                    ?? ($primary?->city ? $primary->city . ', ' . $primary->state : 'N/A');

                return [
                    $customer->customer_code ?? 'N/A',
                    $customer->parentCustomer?->customer_code ?? 'Self',
                    $primary?->name ?? 'N/A',
                    $address,
                    $contact?->mobile_number ?? 'N/A',
                    $contact?->email ?? 'N/A',
                    ($customer->total_bookings ?? 0) . ' Plot',
                    $customer->created_at?->format('d-m-Y') ?? 'N/A',
                ];
            }
        );
    }

    private function buildCustomers(Request $request)
    {
        $query = CustomerBooking::with([
            'primaryDetail.correspondenceDetail',
            'parentCustomer',
            'plotSaleDetails' => function ($q) {
                $q->whereNotNull('booking_code')
                    ->whereHas('payments', function ($paymentQuery) {
                        $paymentQuery->where('booking_status', 'booked');
                    });
            },
            'plotSaleDetails.project',
            'plotSaleDetails.block',
            'plotSaleDetails.plotDetail',
        ])
            ->whereHas('plotSaleDetails', function ($q) {
                $q->whereNotNull('booking_code')
                    ->whereHas('payments', function ($paymentQuery) {
                        $paymentQuery->where('booking_status', 'booked');
                    });
            });

        if ($request->filled('name')) {
            $query->whereHas('primaryDetail', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->name . '%');
            });
        }

        if ($request->filled('mobile')) {
            $query->whereHas('primaryDetail.correspondenceDetail', function ($q) use ($request) {
                $q->where('mobile_number', 'like', '%' . $request->mobile . '%');
            });
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        return $query->latest()
            ->get()
            ->groupBy('customer_code')
            ->map(function ($group) {
                $customer = $group->first();

                $plots = $group->flatMap(function ($booking) {
                    return $booking->plotSaleDetails;
                })
                    ->filter(fn($plotSale) => !empty($plotSale->booking_code))
                    ->values();

                $customer->booked_plots = $plots;
                $customer->total_bookings = $plots->count();

                return $customer;
            })
            ->values();
    }
}
