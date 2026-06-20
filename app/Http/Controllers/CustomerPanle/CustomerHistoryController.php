<?php

namespace App\Http\Controllers\CustomerPanle;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CustomerDocument;
use App\Models\CustomerPayment;
use App\Models\Support;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CustomerHistoryController extends Controller
{
    public function profile(Request $request)
    {
        $customer = auth()->guard('customer')->user();
        $customer->load([
            'primaryDetail.correspondenceDetail',
            'plotSaleDetails.project',
            'plotSaleDetails.block',
            'plotSaleDetails.plotDetail',
            'payments',
        ]);
        $plots = $customer->plotSaleDetails;
        $payments = $customer->payments;
        $totalBooking = $plots->count();
        $totalPlotCost = $plots->sum(function ($plot) {
            return $plot->total_plot_cost ?? $plot->total_amount ?? 0;
        });
        $totalPaid = $payments->sum(function ($payment) {
            return $payment->booking_amount ?? $payment->paid_amount ?? 0;
        });
        $dueAmount = max($totalPlotCost - $totalPaid, 0);
        $paidPercent = $totalPlotCost > 0 ? min(round(($totalPaid / $totalPlotCost) * 100), 100) : 0;
        $latestPlot = $plots->sortByDesc('created_at')->first();
        $latestPayment = $payments->sortByDesc('created_at')->first();

        return view('customer-panel.profile.index', compact(
            'customer',
            'plots',
            'payments',
            'totalBooking',
            'totalPlotCost',
            'totalPaid',
            'dueAmount',
            'paidPercent',
            'latestPlot',
            'latestPayment'
        ));
    }

    public function manageProfile(Request $request)
    {
        $customer = auth()->guard('customer')->user();
        $customer->load([
            'primaryDetail.customerDocument',
            'primaryDetail.correspondenceDetail',
        ]);
        return view('customer-panel.profile.manage', compact('customer'));
    }

    public function updateManageProfile(Request $request)
    {
        $customer = auth()->guard('customer')->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'name.required' => 'Name is required.',
            'name.max' => 'Name must not be greater than 255 characters.',
            'profile_picture.image' => 'Please upload a valid image file.',
            'profile_picture.mimes' => 'Profile image must be JPG, JPEG, PNG, or WEBP.',
            'profile_picture.max' => 'Profile image must not be greater than 2 MB.',
        ]);

        $customer->load('primaryDetail.customerDocument');
        $customer->update([
            'customer_name' => $validated['name'],
        ]);
        $primaryDetail = $customer->primaryDetail ?: $customer->primaryDetail()->create([
            'name' => $validated['name'],
        ]);
        $primaryDetail->update([
            'name' => $validated['name'],
        ]);
        if ($request->hasFile('profile_picture')) {
            $document = $primaryDetail->customerDocument ?: new CustomerDocument([
                'primary_detail_id' => $primaryDetail->id,
            ]);
            $document->profile_picture = uploadFile(
                $request->file('profile_picture'),
                'customer-documents',
                $document->profile_picture
            );
            $document->save();
        }

        return redirect()
            ->route('customer-panel.manage-profile')
            ->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $customer = auth()->guard('customer')->user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'current_password.required' => 'Current password is required.',
            'password.required' => 'New password is required.',
            'password.min' => 'New password must be at least 8 characters.',
            'password.confirmed' => 'New password and confirm password do not match.',
        ]);

        if (!Hash::check($validated['current_password'], $customer->password)) {
            return back()
                ->withErrors(['current_password' => 'Current password is incorrect.'])
                ->onlyInput('current_password');
        }

        $customer->update([
            'password' => Hash::make($validated['password']),
            'plain_password' => $validated['password'],
        ]);

        return redirect()
            ->route('customer-panel.manage-profile')
            ->with('password_success', 'Password changed successfully.');
    }

    public function bookingHistory(Request $request)
    {
        $customer = auth()->guard('customer')->user();

        $bookings = $customer->plotSaleDetails()
            ->with(['project', 'block', 'plotDetail', 'payments'])->whereNotNull('booking_code')->latest()->get()
            ->map(function ($booking) {
                $payments = $booking->payments;
                $confirmedPayments = $payments->where('booking_status', 'booked');
                $latestPayment = $payments->sortByDesc('id')->first();

                $totalCost = (float) ($booking->total_plot_cost ?? $booking->final_payable ?? 0);
                $totalPaid = (float) $confirmedPayments->sum(function ($payment) {
                    return $payment->paid_amount ?? $payment->booking_amount ?? 0;
                });

                $booking->total_cost_amount = $totalCost;
                $booking->confirmed_paid_amount = $totalPaid;
                $booking->due_amount_value = max(0, $totalCost - $totalPaid);
                $booking->latest_payment_status = $latestPayment?->payment_status ?? 'pending';
                $booking->latest_booking_status = $latestPayment?->booking_status ?? 'hold';
                $booking->payment_count = $payments->count();

                return $booking;
            });

        $totalCost = $bookings->sum('total_cost_amount');
        $totalPaid = $bookings->sum('confirmed_paid_amount');
        $totalDue = $bookings->sum('due_amount_value');

        return view('customer-panel.booking-history.index', compact('customer', 'bookings', 'totalCost', 'totalPaid', 'totalDue'));
    }

    public function paymentHistory(Request $request)
    {
        $customer = auth()->guard('customer')->user();

        $payments = $customer->payments()
            ->with(['plotSaleDetail.project', 'plotSaleDetail.block', 'plotSaleDetail.plotDetail'])->latest()->get();

        $confirmedPaid = $payments->where('booking_status', 'booked')
            ->sum(fn($payment) => $payment->paid_amount ?? $payment->booking_amount ?? 0);

        $holdAmount = $payments->where('booking_status', 'hold')
            ->sum(fn($payment) => $payment->paid_amount ?? $payment->booking_amount ?? 0);

        $plotDueTotal = $payments->pluck('plotSaleDetail')->filter()->unique('id')
            ->sum(function ($plotSale) {
                $totalCost = (float) ($plotSale->total_plot_cost ?? $plotSale->final_payable ?? 0);
                $paid = (float) $plotSale->payments()->where('booking_status', 'booked')->sum('paid_amount');
                return max(0, $totalCost - $paid);
            });
        return view('customer-panel.payment-history.index', compact('payments', 'confirmedPaid', 'holdAmount', 'plotDueTotal'));
    }

    public function downloadReceipt($paymentId)
    {
        $payment = CustomerPayment::with([
            'customerBooking.primaryDetail',
            'plotSaleDetail.project',
            'plotSaleDetail.block',
            'plotSaleDetail.plotDetail',
        ])->where('customer_booking_id', auth()->guard('customer')->id())->findOrFail($paymentId);
        $company = Company::where('status', '1')->first();
        $pdf = Pdf::loadView('payment.receipt-reprint.pdf', compact('payment', 'company'));
        return $pdf->download('receipt-' . ($payment->receipt_number ?? 'RCP-' . $payment->id) . '.pdf');
    }

    public function myPlotBooking(Request $request)
    {
        $customer = auth()->guard('customer')->user();
        $plots = $customer->plotSaleDetails()
            ->with(['project', 'block', 'plotDetail', 'payments',])->whereNotNull('booking_code')->latest()->get()
            ->map(function ($plot) {
                $payments = $plot->payments;
                $confirmedPayments = $payments->where('booking_status', 'booked');
                $holdPayments = $payments->where('booking_status', 'hold');
                $latestPayment = $payments->sortByDesc('id')->first();

                $totalCost = (float) ($plot->total_plot_cost ?? $plot->final_payable ?? 0);
                $confirmedPaid = (float) $confirmedPayments->sum(fn($payment) => $payment->paid_amount ?? $payment->booking_amount ?? 0);
                $holdAmount = (float) $holdPayments->sum(fn($payment) => $payment->paid_amount ?? $payment->booking_amount ?? 0);

                $plot->total_cost_amount = $totalCost;
                $plot->confirmed_paid_amount = $confirmedPaid;
                $plot->hold_amount = $holdAmount;
                $plot->due_amount_value = max(0, $totalCost - $confirmedPaid);
                $plot->paid_percent = $totalCost > 0 ? min(round(($confirmedPaid / $totalCost) * 100), 100) : 0;
                $plot->latest_payment_status = $latestPayment?->payment_status ?? 'pending';
                $plot->latest_booking_status = $latestPayment?->booking_status ?? 'hold';
                $plot->payment_count = $payments->count();
                return $plot;
            });

        $totalCost = $plots->sum('total_cost_amount');
        $totalPaid = $plots->sum('confirmed_paid_amount');
        $totalDue = $plots->sum('due_amount_value');

        return view('customer-panel.plot-histroy.index', compact('plots', 'totalCost', 'totalPaid', 'totalDue'));
    }

    public function support(Request $request)
    {
        $enquiries = Support::where('customer_booking_id', auth()->guard('customer')->id())->latest()->get();
        return view('customer-panel.support.index', compact('enquiries'));
    }

    public function supportStore(Request $request)
    {
        $request->validate([
            'query' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        Support::create([
            'customer_booking_id' => auth()->guard('customer')->id(),
            'query' => $request->input('query'),
            'description' => $request->input('description'),
            'status' => 'Pending',
        ]);
        return redirect()->route('customer-panel.support')->with('success', 'Support ticket submitted successfully!');
    }
}