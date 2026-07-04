<?php

namespace App\Services;

use App\Models\CustomerBooking;
use App\Models\CustomerPayment;
use App\Models\PlotDetail;
use Illuminate\Support\Facades\DB;

class ChequeClearanceService
{
    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $paymentIds = collect(explode(',', $data['payment_ids']))
                ->map(fn ($id) => (int) trim($id))
                ->filter()
                ->unique()
                ->values();

            $payments = CustomerPayment::whereIn('id', $paymentIds)->get();
            $promotableBookingIds = collect();

            foreach ($payments as $payment) {

                $isCleared = $data['cheque_status'] === 'cleared';

                $payment->update([
                    'cheque_status' => $data['cheque_status'],
                    'booking_status' => $isCleared ? 'booked' : 'hold',
                    'payment_status' => $isCleared ? 'paid' : 'hold',
                    'cheque_reason' => $data['cheque_reason'] ?? null,
                    'cheque_date' => $data['cheque_date'],
                ]);

                if ($isCleared) {
                    CustomerBooking::where('id', $payment->customer_booking_id)
                        ->update([
                            'current_step' => 5,
                            'status' => 'completed',
                        ]);
                } else {
                    CustomerBooking::where('id', $payment->customer_booking_id)
                        ->update([
                            'current_step' => 5,
                            'status' => 'pending',
                        ]);
                }

                if ($isCleared && $payment->plotSaleDetail?->plot_detail_id) {
                    PlotDetail::where('id', $payment->plotSaleDetail->plot_detail_id)
                        ->update([
                            'status' => 'booked',
                        ]);

                    if ($payment->plan_type === 'emi_plan' && (float) $payment->due_amount <= 0) {
                        CustomerPayment::where('customer_booking_id', $payment->customer_booking_id)
                            ->where('plot_sale_detail_id', $payment->plot_sale_detail_id)
                            ->where('plan_type', $payment->plan_type)
                            ->where('booking_status', 'booked')
                            ->whereIn('payment_status', ['pending', 'paid'])
                            ->update(['payment_status' => 'cleared']);
                    } elseif ($payment->plan_type === 'full_payment') {
                        $totalPlotCost = (float) ($payment->plotSaleDetail->total_plot_cost ?? 0);
                        $confirmedPaid = (float) CustomerPayment::where('customer_booking_id', $payment->customer_booking_id)
                            ->where('plot_sale_detail_id', $payment->plot_sale_detail_id)
                            ->where('plan_type', $payment->plan_type)
                            ->where('booking_status', 'booked')
                            ->sum('paid_amount');

                        if ($totalPlotCost > 0 && $confirmedPaid >= $totalPlotCost) {
                            CustomerPayment::where('customer_booking_id', $payment->customer_booking_id)
                                ->where('plot_sale_detail_id', $payment->plot_sale_detail_id)
                                ->where('plan_type', $payment->plan_type)
                                ->where('booking_status', 'booked')
                                ->whereIn('payment_status', ['pending', 'paid'])
                                ->update(['payment_status' => 'cleared']);
                        }
                    }
                }

                if ($isCleared) {
                    $promotableBookingIds->push($payment->customer_booking_id);
                }
            }

            $promotableBookingIds
                ->filter()
                ->unique()
                ->each(fn ($bookingId) => app(AutoPromotionService::class)->runForBooking((int) $bookingId));

            return true;
        });
    }
}
