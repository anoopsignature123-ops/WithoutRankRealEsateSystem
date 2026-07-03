<?php

namespace App\Http\Controllers\CustomerPanle;

use App\Http\Controllers\Controller;
use App\Models\CustomerDocument;
use App\Models\CustomerPayment;
use App\Models\Support;
use App\Services\ReceiptPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;

class CustomerHistoryController extends Controller
{
    public function profile(Request $request)
    {
        $customer = auth()->guard('customer')->user();
        $customer->load([
            'primaryDetail.correspondenceDetail',
            'plotSaleDetails' => fn ($query) => $query->whereHas('payments', function ($paymentQuery) {
                $paymentQuery->where('booking_status', 'booked');
            }),
            'plotSaleDetails.project',
            'primaryDocument',
            'plotSaleDetails.block',
            'plotSaleDetails.plotDetail',
            'payments' => fn ($query) => $query->where('booking_status', 'booked'),
            'payments.plotSaleDetail.plotDetail',
        ]);
        $plots = $customer->plotSaleDetails;
        $payments = $customer->payments;
        $totalBooking = $plots->whereNotNull('booking_code')->count();
        $totalPlotCost = $plots->sum(function ($plot) {
            return $plot->total_plot_cost ?? $plot->total_amount ?? 0;
        });
        $totalPaid = $payments
            ->whereIn('payment_status', ['paid', 'cleared'])
            ->sum(function ($payment) {
                return $payment->paid_amount ?? $payment->booking_amount ?? 0;
        });
        $dueAmount = max($totalPlotCost - $totalPaid, 0);
        $paidPercent = $totalPlotCost > 0 ? min(round(($totalPaid / $totalPlotCost) * 100), 100) : 0;
        $bookingGroups = $plots
            ->whereNotNull('booking_code')
            ->groupBy(fn ($plot) => $plot->booking_code ?: 'plot-'.$plot->id)
            ->map(function (Collection $group) use ($payments) {
                $first = $group->first();
                $groupPayments = $payments->whereIn('plot_sale_detail_id', $group->pluck('id'));
                $groupCost = $group->sum(fn ($plot) => (float) ($plot->total_plot_cost ?? $plot->final_payable ?? 0));
                $groupPaid = $groupPayments
                    ->whereIn('payment_status', ['paid', 'cleared'])
                    ->sum(fn ($payment) => (float) ($payment->paid_amount ?? $payment->booking_amount ?? 0));

                return [
                    'booking_code' => $first?->booking_code ?? '-',
                    'project' => $group->pluck('project.name')->filter()->unique()->implode(', ') ?: 'N/A',
                    'block' => $group->pluck('block.block')->filter()->unique()->implode(', ') ?: 'N/A',
                    'plots' => $group->pluck('plotDetail.plot_number')->filter()->implode(', ') ?: 'N/A',
                    'plot_count' => $group->count(),
                    'total_area' => $group->sum(fn ($plot) => (float) ($plot->plot_area ?? 0)),
                    'total_cost' => $groupCost,
                    'paid' => $groupPaid,
                    'due' => max($groupCost - $groupPaid, 0),
                    'created_at' => $group->max('created_at'),
                    'booking_date' => $first?->booking_date,
                ];
            })
            ->sortByDesc('created_at')
            ->values();
        $latestBooking = $bookingGroups->first();
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
            'bookingGroups',
            'latestBooking',
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

        $plots = $customer->plotSaleDetails()
            ->with([
                'project',
                'block',
                'plotDetail',
                'payments' => fn ($query) => $query->where('booking_status', 'booked'),
            ])
            ->whereHas('payments', function ($query) {
                $query->where('booking_status', 'booked');
            })
            ->whereNotNull('booking_code')
            ->latest()
            ->get();

        $bookings = $plots
            ->groupBy(fn ($plot) => $plot->booking_code ?: 'plot-'.$plot->id)
            ->map(function (Collection $group) {
                $first = $group->first();
                $payments = $group->flatMap(function ($plot) {
                    return $plot->payments;
                })->values();
                $confirmedPayments = $payments->whereIn('payment_status', ['paid', 'cleared']);
                $latestPayment = $payments->sortByDesc('id')->first();
                $totalCost = $group->sum(fn ($plot) => (float) ($plot->total_plot_cost ?? $plot->final_payable ?? 0));
                $totalPaid = (float) $confirmedPayments->sum(function ($payment) {
                    return $payment->paid_amount ?? $payment->booking_amount ?? 0;
                });
                $planTypes = $payments->pluck('plan_type')->filter()->unique()->values();
                $plotStatuses = $group->pluck('plotDetail.status')->filter()->unique()->values();

                return (object) [
                    'id' => $first?->id,
                    'booking_code' => $first?->booking_code ?? 'N/A',
                    'project_name' => $group->pluck('project.name')->filter()->unique()->implode(', ') ?: 'N/A',
                    'block_name' => $group->pluck('block.block')->filter()->unique()->implode(', ') ?: 'N/A',
                    'plot_numbers' => $group->pluck('plotDetail.plot_number')->filter()->implode(', ') ?: 'N/A',
                    'plot_count' => $group->count(),
                    'total_area' => $group->sum(fn ($plot) => (float) ($plot->plot_area ?? $plot->plotDetail?->plot_area ?? 0)),
                    'total_cost_amount' => $totalCost,
                    'confirmed_paid_amount' => $totalPaid,
                    'due_amount_value' => max(0, $totalCost - $totalPaid),
                    'paid_percent' => $totalCost > 0 ? min(round(($totalPaid / $totalCost) * 100), 100) : 0,
                    'latest_payment_status' => $latestPayment?->payment_status ?? 'pending',
                    'latest_booking_status' => $latestPayment?->booking_status ?? 'hold',
                    'payment_count' => $payments->count(),
                    'plan_type' => $planTypes->count() > 1 ? 'mixed' : ($planTypes->first() ?? 'full_payment'),
                    'plot_status' => $plotStatuses->count() > 1 ? 'mixed' : ($plotStatuses->first() ?? 'booked'),
                    'booking_date' => $first?->booking_date,
                    'created_at' => $group->max('created_at'),
                    'plots' => $group->values(),
                    'payments' => $payments,
                ];
            })
            ->sortByDesc('created_at')
            ->values();

        $totalCost = $bookings->sum('total_cost_amount');
        $totalPaid = $bookings->sum('confirmed_paid_amount');
        $totalDue = $bookings->sum('due_amount_value');

        return view('customer-panel.booking-history.index', compact('customer', 'bookings', 'totalCost', 'totalPaid', 'totalDue'));
    }

    public function paymentHistory(Request $request)
    {
        $customer = auth()->guard('customer')->user();

        $payments = $customer->payments()
            ->with(['plotSaleDetail.project', 'plotSaleDetail.block', 'plotSaleDetail.plotDetail'])
            ->where('booking_status', 'booked')
            ->latest()
            ->get();

        $paymentRecords = $payments
            ->groupBy(fn ($payment) => $payment->receipt_number ?: 'payment-'.$payment->id)
            ->map(function (Collection $group) {
                $first = $group->sortByDesc('id')->first();
                $plots = $group->pluck('plotSaleDetail')->filter()->unique('id')->values();
                $amount = (float) $group->sum(fn ($payment) => $payment->paid_amount ?? $payment->booking_amount ?? 0);
                $dueAmount = (float) $group->sum(fn ($payment) => $payment->due_amount ?? 0);
                $netPayable = (float) $plots->sum(fn ($plot) => $plot->total_plot_cost ?? $plot->final_payable ?? 0);
                $paymentStatuses = $group->pluck('payment_status')->filter()->unique()->values();
                $bookingStatuses = $group->pluck('booking_status')->filter()->unique()->values();
                $planTypes = $group->pluck('plan_type')->filter()->unique()->values();

                return (object) [
                    'id' => $first->id,
                    'receipt_number' => $first->receipt_number ?? 'N/A',
                    'manual_receipt_number' => $first->manual_receipt_number,
                    'payments' => $group->values(),
                    'plots' => $plots,
                    'plot_count' => $plots->count(),
                    'plot_numbers' => $plots->pluck('plotDetail.plot_number')->filter()->implode(', ') ?: 'N/A',
                    'booking_codes' => $plots->pluck('booking_code')->filter()->unique()->implode(', ') ?: ($first->customerBooking?->booking_code ?? 'N/A'),
                    'project_names' => $plots->pluck('project.name')->filter()->unique()->implode(', ') ?: 'N/A',
                    'amount' => $amount,
                    'due_amount' => $dueAmount,
                    'net_payable_amount' => $netPayable,
                    'payment_mode' => $first->payment_mode,
                    'payment_status' => $paymentStatuses->count() > 1 ? 'mixed' : ($paymentStatuses->first() ?? 'pending'),
                    'booking_status' => $bookingStatuses->count() > 1 ? 'mixed' : ($bookingStatuses->first() ?? 'hold'),
                    'plan_type' => $planTypes->count() > 1 ? 'mixed' : ($planTypes->first() ?? 'full_payment'),
                    'transaction_category' => $first->transaction_category,
                    'created_at' => $first->created_at,
                    'representative' => $first,
                ];
            })
            ->sortByDesc('created_at')
            ->values();

        $confirmedPaid = $payments->whereIn('payment_status', ['paid', 'cleared'])
            ->sum(fn($payment) => $payment->paid_amount ?? $payment->booking_amount ?? 0);

        $holdAmount = $payments->where('payment_status', 'hold')
            ->sum(fn($payment) => $payment->paid_amount ?? $payment->booking_amount ?? 0);

        $plotDueTotal = $payments->pluck('plotSaleDetail')->filter()->unique('id')
            ->sum(function ($plotSale) {
                $totalCost = (float) ($plotSale->total_plot_cost ?? $plotSale->final_payable ?? 0);
                $paid = (float) $plotSale->payments()
                    ->whereIn('payment_status', ['paid', 'cleared'])
                    ->sum('paid_amount');
                return max(0, $totalCost - $paid);
            });
        return view('customer-panel.payment-history.index', compact('payments', 'paymentRecords', 'confirmedPaid', 'holdAmount', 'plotDueTotal'));
    }

    public function downloadReceipt($paymentId)
    {
        $payment = CustomerPayment::with([
            'customerBooking.primaryDetail',
            'plotSaleDetail.project',
            'plotSaleDetail.block',
            'plotSaleDetail.plotDetail',
        ])->where('customer_booking_id', auth()->guard('customer')->id())->findOrFail($paymentId);
        abort_unless($payment->booking_status === 'booked', 404);
        return app(ReceiptPdfService::class)->download($payment);
    }

    public function myPlotBooking(Request $request)
    {
        $customer = auth()->guard('customer')->user();
        $plots = $customer->plotSaleDetails()
            ->with([
                'project',
                'block',
                'plotDetail',
                'payments' => fn ($query) => $query->where('booking_status', 'booked'),
            ])
            ->whereHas('payments', function ($query) {
                $query->where('booking_status', 'booked');
            })
            ->whereNotNull('booking_code')
            ->latest()
            ->get();

        $bookings = $plots
            ->groupBy(fn ($plot) => $plot->booking_code ?: 'plot-'.$plot->id)
            ->map(function (Collection $group) {
                $first = $group->first();
                $payments = $group->flatMap(function ($plot) {
                    return $plot->payments;
                })->values();
                $confirmedPayments = $payments->whereIn('payment_status', ['paid', 'cleared']);
                $holdPayments = $payments->where('payment_status', 'hold');
                $latestPayment = $payments->sortByDesc('id')->first();
                $totalCost = $group->sum(fn ($plot) => (float) ($plot->total_plot_cost ?? $plot->final_payable ?? 0));
                $confirmedPaid = (float) $confirmedPayments->sum(fn ($payment) => $payment->paid_amount ?? $payment->booking_amount ?? 0);
                $holdAmount = (float) $holdPayments->sum(fn ($payment) => $payment->paid_amount ?? $payment->booking_amount ?? 0);
                $planTypes = $payments->pluck('plan_type')->filter()->unique()->values();
                $plotStatuses = $group->pluck('plotDetail.status')->filter()->unique()->values();

                return (object) [
                    'id' => $first?->id,
                    'booking_code' => $first?->booking_code ?? 'N/A',
                    'project_name' => $group->pluck('project.name')->filter()->unique()->implode(', ') ?: 'N/A',
                    'block_name' => $group->pluck('block.block')->filter()->unique()->implode(', ') ?: 'N/A',
                    'plot_numbers' => $group->pluck('plotDetail.plot_number')->filter()->implode(', ') ?: 'N/A',
                    'plot_count' => $group->count(),
                    'total_area' => $group->sum(fn ($plot) => (float) ($plot->plot_area ?? $plot->plotDetail?->plot_area ?? 0)),
                    'total_cost_amount' => $totalCost,
                    'confirmed_paid_amount' => $confirmedPaid,
                    'hold_amount' => $holdAmount,
                    'due_amount_value' => max(0, $totalCost - $confirmedPaid),
                    'paid_percent' => $totalCost > 0 ? min(round(($confirmedPaid / $totalCost) * 100), 100) : 0,
                    'latest_payment_status' => $latestPayment?->payment_status ?? 'pending',
                    'latest_booking_status' => $latestPayment?->booking_status ?? 'hold',
                    'payment_count' => $payments->count(),
                    'plan_type' => $planTypes->count() > 1 ? 'mixed' : ($planTypes->first() ?? 'full_payment'),
                    'plot_status' => $plotStatuses->count() > 1 ? 'mixed' : ($plotStatuses->first() ?? 'booked'),
                    'booking_date' => $first?->booking_date,
                    'created_at' => $group->max('created_at'),
                    'plots' => $group->values(),
                    'payments' => $payments,
                ];
            })
            ->sortByDesc('created_at')
            ->values();

        $totalCost = $bookings->sum('total_cost_amount');
        $totalPaid = $bookings->sum('confirmed_paid_amount');
        $totalDue = $bookings->sum('due_amount_value');

        return view('customer-panel.plot-histroy.index', compact('bookings', 'plots', 'totalCost', 'totalPaid', 'totalDue'));
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
