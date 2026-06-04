<?php

namespace App\Http\Controllers;

use App\Services\BookingLetterService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class BookingLetterController extends Controller
{
    public function __construct(
        private BookingLetterService $service
    ) {}

    public function index(Request $request)
    {
        $bookings = $this->service->getBookings($request->booking_id);

        $bookingList = $this->service->getBookingDropdown();

        return view('customer-booking.booking-letter.index', compact(
            'bookings',
            'bookingList'
        ));
    }

    public function agreementLetter($id)
    {
        $booking = $this->service->findBooking($id);

        return view('customer-booking.booking-letter.agreement-letter', compact('booking'));
    }

    public function allotementPdf($id)
    {
        $booking = $this->service->findBooking($id);

        $pdf = Pdf::loadView('customer-booking.booking-letter.allotement-letter', compact('booking'));

        $pdf->setPaper('A4');

        return $pdf->download('allotement-letter-' . ($booking->booking_code ?? $booking->id) . '.pdf');
    }

    public function agreementPdf($id)
    {
        $booking = $this->service->findBooking($id);

        $pdf = Pdf::loadView('customer-booking.booking-letter.agreement-letter', compact('booking'));

        $pdf->setPaper('A4');

        return $pdf->download('agreement-letter-' . ($booking->booking_code ?? $booking->id) . '.pdf');
    }
}