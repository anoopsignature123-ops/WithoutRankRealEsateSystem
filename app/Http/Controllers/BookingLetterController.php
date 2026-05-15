<?php

namespace App\Http\Controllers;

use App\Services\BookingLetterService;
use Illuminate\Http\Request;

class BookingLetterController extends Controller
{
    public function __construct(
        private BookingLetterService $service
    ) {}

    public function index(Request $request)
    {
        $bookings = $this->service->getBookings(
            $request->booking_id
        );

        $bookingList = $this->service->getBookingDropdown();

        return view(
            'customer-booking.booking-letter.index',
            compact(
                'bookings',
                'bookingList'
            )
        );
    }

    public function allotementLetter($id)
    {
        $booking = $this->service->findBooking($id);

        return view(
            'customer-booking.booking-letter.allotement-letter',
            compact('booking')
        );
    }

    public function agreementLetter($id)
    {
        $booking = $this->service->findBooking($id);

        return view(
            'customer-booking.booking-letter.agreement-letter',
            compact('booking')
        );
    }
}
