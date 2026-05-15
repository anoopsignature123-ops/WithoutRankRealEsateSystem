<?php

namespace App\Http\Controllers;

use App\Services\CustomerListService;

class CustomerListController extends Controller
{
    protected $customerListService;

    public function __construct(CustomerListService $customerListService) {
        $this->customerListService = $customerListService;
    }

    // Customer List
    public function index()
    {
        $customers = $this->customerListService->getAllCustomers();
        return view('customer-list.index',compact('customers'));
    }

    // Edit Plot Booking List
    public function editPlotBooking()
    {
        $customers = $this->customerListService->getPlotBookingList();
        return view('edit-plot-booking.index',compact('customers'));
    }
}
