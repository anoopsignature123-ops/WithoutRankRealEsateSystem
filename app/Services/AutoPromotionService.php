<?php

namespace App\Services;

use App\Models\Associate;
use App\Models\CustomerBooking;
use App\Models\CustomerPayment;
use Illuminate\Support\Collection;

class AutoPromotionService
{
    public function __construct(
        private AssociatePromotionService $promotionService
    ) {}

    public function runForPayment(CustomerPayment|int|null $payment): Collection
    {
        if (! $payment) {
            return collect();
        }

        $payment = $payment instanceof CustomerPayment
            ? $payment
            : CustomerPayment::find($payment);

        if (! $payment || ! $this->isEligiblePayment($payment)) {
            return collect();
        }

        return $this->runForBooking((int) $payment->customer_booking_id);
    }

    public function runForBooking(CustomerBooking|int|null $booking): Collection
    {
        if (! $booking) {
            return collect();
        }

        $booking = $booking instanceof CustomerBooking
            ? $booking
            : CustomerBooking::find($booking);

        if (! $booking || ! $booking->associate_id) {
            return collect();
        }

        return $this->runForAssociate((int) $booking->associate_id);
    }

    public function runForAssociate(Associate|int|null $associate): Collection
    {
        if (! $associate) {
            return collect();
        }

        $associate = $associate instanceof Associate
            ? $associate
            : Associate::find($associate);

        if (! $associate) {
            return collect();
        }

        return $this->promotionChain($associate)
            ->map(fn (Associate $chainAssociate) => $this->promotionService->checkPromotionResult($chainAssociate->id))
            ->values();
    }

    public function isEligiblePayment(CustomerPayment $payment): bool
    {
        return $payment->booking_status === 'booked'
            && in_array($payment->payment_status, ['paid', 'cleared'], true)
            && (float) ($payment->paid_amount ?? $payment->booking_amount ?? 0) > 0;
    }

    private function promotionChain(Associate $associate): Collection
    {
        $chain = collect();
        $seen = [];
        $current = $associate;

        while ($current && ! in_array($current->id, $seen, true)) {
            $seen[] = $current->id;
            $chain->push($current);

            $uplineCode = $current->under_place_id ?: $current->sponsor_id;

            if (! $uplineCode) {
                break;
            }

            $current = Associate::where('associate_id', $uplineCode)->first();
        }

        return $chain;
    }
}
