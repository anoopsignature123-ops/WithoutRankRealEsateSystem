<?php

namespace App\Services;

use App\Models\Associate;
use App\Models\Block;
use App\Models\CorrespondenceDetail;
use App\Models\CustomerBooking;
use App\Models\CustomerDocument;
use App\Models\CustomerPayment;
use App\Models\PlotDetail;
use App\Models\PlotSaleDetail;
use App\Models\PrimaryDetail;
use App\Models\Project;
use App\Models\SecondaryDetail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CustomerBookingService
{
    public function getAll()
    {
        return CustomerBooking::with([
            'primaryDetail.customerDocument',
            'primaryDetail.correspondenceDetail',
            'plotSaleDetail.project',
            'plotSaleDetail.block',
            'plotSaleDetail.plotDetail',
        ])->latest()->get();
    }

    public function getAssociates()
    {
        return Associate::select('id', 'associate_id', 'associate_name')->get();
    }

    public function getCustomers()
    {
        return CustomerBooking::select('id', 'customer_code', 'customer_name')
            ->whereNotNull('customer_code')
            ->get();
    }

    public function findById($id)
    {
        return CustomerBooking::with([
            'primaryDetail.customerDocument',
            'secondaryDetail.customerDocument',
            'plotSaleDetail.project',
            'plotSaleDetail.block',
            'plotSaleDetail.plotDetail',
            'plotSaleDetails.project',
            'plotSaleDetails.block',
            'plotSaleDetails.plotDetail',
            'plotSaleDetails.payments',
            'payment',
            'payments',
        ])->findOrFail($id);
    }

    public function getPrimaryDetail($customerId)
    {
        return PrimaryDetail::where('customer_booking_id', $customerId)->first();
    }

    public function getSecondaryDetail($customerId)
    {
        return SecondaryDetail::where('customer_booking_id', $customerId)->first();
    }

    public function storeStepOne(array $data, $customerId = null)
    {
        $customerCode = null;
        if (!$customerId) {
            $lastId = CustomerBooking::max('id') + 1;
            $customerCode = 'CUST-' . str_pad($lastId, 4, '0', STR_PAD_LEFT);
        }

        return CustomerBooking::updateOrCreate(['id' => $customerId], [
            'associate_id' => $data['associate_id'] ?? null,
            'customer_type' => $data['customer_type'] ?? null,
            'customer_id' => $data['existing_customer_id'] ?? null,
            'customer_code' => $customerCode ?? CustomerBooking::find($customerId)?->customer_code,
            'associate_code' => $data['associate_code'] ?? null,
            'associate_name' => $data['associate_name'] ?? null,
            'current_step' => 2,
            'status' => 'draft',
        ]);
    }

    public function storeStepTwo($customerId, array $data)
    {

        
        $primary = PrimaryDetail::updateOrCreate(['customer_booking_id' => $customerId], [
            'name' => $data['name'],
            'title' => $data['title'],
            'relation_name' => $data['relation_name'],
            'dob' => $data['dob'],
            'gender' => $data['gender'],
            'permanent_address' => $data['permanent_address'],
            'pin_code' => $data['pin_code'],
            'city' => $data['city'],
            'state' => $data['state'],
            'fill_secondary_detail' => $data['fill_secondary_detail'],
        ]);

        CorrespondenceDetail::updateOrCreate(['primary_detail_id' => $primary->id], [
            'correspondence_address' => $data['correspondence_address'],
            'pin_code' => $data['pin_code'],
            'city' => $data['city'],
            'state' => $data['state'],
            'mobile_number' => $data['mobile_number'] ?? null,
            'email' => $data['email'] ?? null,
        ]);

        if ($data['fill_secondary_detail'] == 'yes') {
            $secondary = SecondaryDetail::updateOrCreate(['customer_booking_id' => $customerId], [
                'name' => $data['secondary_name'],
                'title' => $data['secondary_title'],
                'relation_name' => $data['secondary_relation_name'],
                'dob' => $data['secondary_dob'],
                'gender' => $data['secondary_gender'],
                'permanent_address' => $data['secondary_permanent_address'],
                'pin_code' => $data['secondary_pin_code'],
                'city' => $data['secondary_city'],
                'state' => $data['secondary_state'],
            ]);

            CorrespondenceDetail::updateOrCreate(['secondary_detail_id' => $secondary->id], [
                'correspondence_address' => $data['secondary_correspondence_address'],
                'pin_code' => $data['secondary_pin_code'],
                'city' => $data['secondary_city'],
                'state' => $data['secondary_state'],
                'mobile_number' => $data['secondary_mobile_number'] ?? null,
                'email' => $data['secondary_email'] ?? null,
            ]);
        } else {
            SecondaryDetail::where('customer_booking_id', $customerId)->delete();
        }
        $plainPassword = Str::upper(Str::random(8));
        CustomerBooking::where('id', $customerId)->update([
            'current_step' => 3,
            'password' => Hash::make($plainPassword),
            'plain_password' => $plainPassword,
        ]);
    }

    public function storeStepThree($customerId, $request)
    {
        $primary = PrimaryDetail::where('customer_booking_id', $customerId)->first();

        $dlFile = uploadFile($request->file('dl_file'), 'customer-documents');
        $aadharFile = uploadFile($request->file('aadhar_file'), 'customer-documents');
        $voterFile = uploadFile($request->file('voter_id_file'), 'customer-documents');
        $otherFile = uploadFile($request->file('other_file'), 'customer-documents');
        $profilePic = uploadFile($request->file('profile_picture'), 'customer-documents');

        CustomerDocument::updateOrCreate(['primary_detail_id' => $primary->id], [
            'secondary_detail_id' => null,
            'dl' => $request->has('dl'),
            'aadhar' => $request->has('aadhar'),
            'voter_id' => $request->has('voter_id'),
            'other' => $request->has('other'),
            'dl_file' => $dlFile,
            'aadhar_file' => $aadharFile,
            'voter_id_file' => $voterFile,
            'other_file' => $otherFile,
            'profile_picture' => $profilePic,
        ]);

        $secondary = SecondaryDetail::where('customer_booking_id', $customerId)->first();
        if ($secondary) {
            $secondaryDl = uploadFile($request->file('secondary_dl_file'), 'customer-documents');
            $secondaryAadhar = uploadFile($request->file('secondary_aadhar_file'), 'customer-documents');
            $secondaryVoter = uploadFile($request->file('secondary_voter_id_file'), 'customer-documents');
            $secondaryOther = uploadFile($request->file('secondary_other_file'), 'customer-documents');
            $secondaryProfile = uploadFile($request->file('secondary_profile_picture'), 'customer-documents');

            CustomerDocument::updateOrCreate(['secondary_detail_id' => $secondary->id], [
                'primary_detail_id' => null,
                'dl' => $request->has('secondary_dl'),
                'aadhar' => $request->has('secondary_aadhar'),
                'voter_id' => $request->has('secondary_voter_id'),
                'other' => $request->has('secondary_other'),
                'dl_file' => $secondaryDl,
                'aadhar_file' => $secondaryAadhar,
                'voter_id_file' => $secondaryVoter,
                'other_file' => $secondaryOther,
                'profile_picture' => $secondaryProfile,
            ]);
        }

        CustomerBooking::where('id', $customerId)->update(['current_step' => 4]);
    }

    public function getProjects()
    {
        return Project::select('id', 'name')->get();
    }

    public function getPlots()
    {
        return PlotDetail::select('id', 'plot_number')->get();
    }

    public function getBlocksByProject($projectId)
    {
        return Block::where('project_id', $projectId)->get();
    }

    public function getPlotsByBlock($blockId, $customerId = null)
    {
        $bookedPlotIds = PlotSaleDetail::when($customerId, function ($query, $customerId) {
            return $query->where('customer_booking_id', '!=', $customerId);
        })->pluck('plot_detail_id')->toArray();

        return PlotDetail::with('plotType')
            ->where('status', 'available')
            ->where('block_id', $blockId)
            ->whereNotIn('id', $bookedPlotIds)
            ->get();
    }

    public function storeStepFour($customerId, array $data)
    {
        $editBookingCode = $data['edit_booking_code'] ?? null;
        $plotIds = collect($data['plot_detail_ids'] ?? [])
            ->filter()
            ->unique()
            ->values();

        $bookingCode = null;
        if ($editBookingCode) {
            $bookingCode = $editBookingCode;
        }

        if ($plotIds->isNotEmpty()) {
            $plotDetails = collect($data['plot_details'] ?? []);
            if (! $bookingCode) {
                $bookingCode = $this->makePlotBookingCode($customerId);
            }

            $groupHasPayment = PlotSaleDetail::where('customer_booking_id', $customerId)
                ->where('booking_code', $bookingCode)
                ->whereHas('payments', fn ($query) => $query->where('transaction_category', 'booking_fee'))
                ->exists();

            if ($groupHasPayment) {
                $existingPlotIds = PlotSaleDetail::where('customer_booking_id', $customerId)
                    ->where('booking_code', $bookingCode)
                    ->pluck('plot_detail_id')
                    ->filter()
                    ->sort()
                    ->values();
                $selectedPlotIds = $plotIds->sort()->values();

                if ($existingPlotIds->toArray() !== $selectedPlotIds->toArray()) {
                    throw new \Exception('Payment is already done for this booking group. Plot selection cannot be changed.');
                }
            }

            $alreadyUsedPlots = PlotSaleDetail::where('customer_booking_id', $customerId)
                ->whereIn('plot_detail_id', $plotIds->all())
                ->where(function ($query) use ($bookingCode) {
                    $query->whereNull('booking_code')
                        ->orWhere('booking_code', '!=', $bookingCode);
                })
                ->exists();

            if ($alreadyUsedPlots) {
                throw new \Exception('One or more selected plots already belong to another booking group.');
            }

            $baseTotal = $plotIds->sum(function ($plotId) use ($plotDetails) {
                $detail = $plotDetails->get((string) $plotId, $plotDetails->get($plotId, []));

                return (float) ($detail['plot_cost'] ?? 0) + (float) ($detail['plc_amount'] ?? 0);
            });
            $developmentCharge = (float) ($data['total_development_charge'] ?? 0);
            $otherCharges = (float) ($data['other_charges'] ?? 0);
            $couponDiscount = (float) ($data['coupon_discount'] ?? 0);

            PlotSaleDetail::where('customer_booking_id', $customerId)
                ->when($bookingCode, fn ($query) => $query->where('booking_code', $bookingCode))
                ->whereNotIn('plot_detail_id', $plotIds->all())
                ->whereDoesntHave('payments')
                ->delete();

            $savedPlotSales = collect();

            foreach ($plotIds as $index => $plotId) {
                $detail = $plotDetails->get((string) $plotId, $plotDetails->get($plotId, []));
                $plotCost = (float) ($detail['plot_cost'] ?? 0);
                $plcAmount = (float) ($detail['plc_amount'] ?? 0);
                $baseAmount = $plotCost + $plcAmount;
                $ratio = $baseTotal > 0 ? $baseAmount / $baseTotal : (1 / max($plotIds->count(), 1));
                $allocatedDevelopment = isset($detail['total_development_charge'])
                    ? (float) $detail['total_development_charge']
                    : round($developmentCharge * $ratio, 2);
                $allocatedOther = isset($detail['other_charges'])
                    ? (float) $detail['other_charges']
                    : round($otherCharges * $ratio, 2);
                $allocatedDiscount = isset($detail['coupon_discount'])
                    ? (float) $detail['coupon_discount']
                    : round($couponDiscount * $ratio, 2);
                $finalPayable = max(0, $plotCost + $plcAmount + $allocatedDevelopment + $allocatedOther);
                $totalPlotCost = max(0, $finalPayable - $allocatedDiscount);

                if ($index === $plotIds->count() - 1) {
                    $savedBaseTotal = $savedPlotSales->sum('total_plot_cost');
                    $expectedTotal = max(0, $baseTotal + $developmentCharge + $otherCharges - $couponDiscount);
                    $totalPlotCost = round($expectedTotal - $savedBaseTotal, 2);
                    $finalPayable = round($totalPlotCost + $allocatedDiscount, 2);
                }

                $searchAttributes = ['customer_booking_id' => $customerId, 'plot_detail_id' => $plotId];
                if (!empty($detail['sale_id'])) {
                    $searchAttributes = ['id' => $detail['sale_id']];
                }

                $savedPlotSales->push(PlotSaleDetail::updateOrCreate(
                    $searchAttributes,
                    [
                        'booking_code' => $bookingCode,
                        'project_id' => $data['project_id'] ?? null,
                        'block_id' => $data['block_id'] ?? null,
                        'customer_booking_id' => $customerId,
                        'plot_detail_id' => $plotId,
                        'total_development_charge' => $allocatedDevelopment,
                        'development_rate' => $detail['development_rate'] ?? $data['development_rate'] ?? null,
                        'plot_rate' => $detail['plot_rate'] ?? null,
                        'plot_area' => $detail['plot_area'] ?? null,
                        'plot_cost' => $plotCost,
                        'plc_amount' => $plcAmount,
                        'remark' => $detail['remark'] ?? $data['remark'] ?? null,
                        'other_charges' => $allocatedOther,
                        'final_payable' => $finalPayable,
                        'coupon_discount' => $allocatedDiscount,
                        'total_plot_cost' => $totalPlotCost,
                        'booking_date' => $detail['booking_date'] ?? $data['booking_date'] ?? null,
                    ]
                ));
            }

            CustomerBooking::where('id', $customerId)->update(['current_step' => 5]);

            return $savedPlotSales;
        }

        if (! $bookingCode) {
            $bookingCode = $this->makePlotBookingCode($customerId);
        }

        $oldPlotSale = PlotSaleDetail::where('customer_booking_id', $customerId)
            ->where('booking_code', $bookingCode)
            ->latest()
            ->first();

        // Avoid creating a duplicate record when the same plot is already selected.
        if ($oldPlotSale && $oldPlotSale->plot_detail_id == ($data['plot_detail_id'] ?? null)) {
            CustomerBooking::where('id', $customerId)->update(['current_step' => 5]);

            return $oldPlotSale;
        }

        $plotSale = PlotSaleDetail::create([
            'customer_booking_id' => $customerId,
            'booking_code' => $bookingCode,
            'project_id' => $data['project_id'] ?? null,
            'block_id' => $data['block_id'] ?? null,
            'plot_detail_id' => $data['plot_detail_id'] ?? null,
            'total_development_charge' => $data['total_development_charge'] ?? null,
            'development_rate' => $data['development_rate'] ?? null,
            'plot_rate' => $data['plot_rate'] ?? null,
            'plot_area' => $data['plot_area'] ?? null,
            'plot_cost' => $data['plot_cost'] ?? null,
            'plc_amount' => $data['plc_amount'] ?? null,
            'remark' => $data['remark'] ?? null,
            'other_charges' => $data['other_charges'] ?? null,
            'final_payable' => $data['final_payable'] ?? null,
            'coupon_discount' => $data['coupon_discount'] ?? null,
            'total_plot_cost' => $data['total_plot_cost'] ?? null,
            'booking_date' => $data['booking_date'] ?? null,
        ]);

        CustomerBooking::where('id', $customerId)->update(['current_step' => 5]);

        return $plotSale;
    }

    public function storeStepFive($customerId, array $data)
    {
        $paymentMode = $data['payment_mode'] ?? null;
        $planType = $data['plan_type'] ?? null;
        $plotSaleIds = collect($data['plot_sale_detail_ids'] ?? [$data['plot_sale_detail_id'] ?? null])
            ->filter()
            ->unique()
            ->values();
        $transactionNumber = $data['transaction_number'] ?? strtoupper($paymentMode ?: 'PAY') . '-' . time();
        $receiptNumber = $data['receipt_number'] ?? 'REC-' . Str::upper(Str::random(8));
        $isInstantPayment = in_array($paymentMode, ['cash', 'card', 'neft_rtgs'], true);
        $bookingStatus = $isInstantPayment ? 'booked' : 'hold';
        $booking = CustomerBooking::find($customerId);
        $plotSales = PlotSaleDetail::where('customer_booking_id', $customerId)
            ->whereIn('id', $plotSaleIds)
            ->get();
        $totalPayable = round((float) $plotSales->sum('total_plot_cost'), 2);
        $paidTotal = min(round((float) ($data['booking_amount'] ?? 0), 2), $totalPayable);
        $remainingPaid = $paidTotal;

        foreach ($plotSales as $index => $plotSale) {
            $plotPayable = round((float) ($plotSale->total_plot_cost ?? 0), 2);
            $allocatedPaid = $index === $plotSales->count() - 1
                ? $remainingPaid
                : round($totalPayable > 0 ? ($paidTotal * $plotPayable / $totalPayable) : 0, 2);
            $allocatedPaid = min($allocatedPaid, $plotPayable);
            $remainingPaid = round($remainingPaid - $allocatedPaid, 2);
            $dueAmount = max(0, round($plotPayable - $allocatedPaid, 2));
            $paymentStatus = $bookingStatus === 'hold'
                ? 'hold'
                : ($dueAmount <= 0 ? 'cleared' : 'paid');

            $oldPayment = CustomerPayment::where('customer_booking_id', $customerId)
                ->where('plot_sale_detail_id', $plotSale->id)
                ->where('transaction_category', 'booking_fee')
                ->first();

            if (!$oldPayment) {
                CustomerPayment::create([
                    'plan_type' => $planType,
                    'booking_amount' => $allocatedPaid,
                    'paid_amount' => $allocatedPaid,
                    'due_amount' => $dueAmount,
                    'net_payable_amount' => $plotPayable,
                    'emi_months' => $data['emi_months'] ?? null,
                    'emi_date' => now(),
                    'after_booking_payable_amount' => $planType === 'emi_plan' && !empty($data['emi_months'])
                        ? round($dueAmount / max((int) $data['emi_months'], 1), 2)
                        : null,
                    'remark' => $data['remark'] ?? null,
                    'payment_mode' => $paymentMode,
                    'account_number' => $data['account_number'] ?? null,
                    'bank_name' => $data['bank_name'] ?? null,
                    'branch_name' => $data['branch_name'] ?? null,
                    'cheque_number' => $data['cheque_number'] ?? null,
                    'cheque_date' => $data['cheque_date'] ?? null,
                    'dd_number' => $data['dd_number'] ?? null,
                    'transaction_number' => $transactionNumber,
                    'booking_status' => $bookingStatus,
                    'payment_status' => $paymentStatus,
                    'receipt_number' => $receiptNumber,
                    'customer_booking_id' => $customerId,
                    'plot_sale_detail_id' => $plotSale->id,
                    'transaction_category' => 'booking_fee',
                ]);
            }

            if ($plotSale->plot_detail_id) {
                $newStatus = $isInstantPayment ? 'booked' : 'hold';
                PlotDetail::where('id', $plotSale->plot_detail_id)->update(['status' => $newStatus]);
            }

            if (!$plotSale->booking_code) {
                $plotSale->update([
                    'booking_code' => $this->makePlotBookingCode($customerId),
                ]);
            }
        }

        $booking->update([
            'current_step' => 5,
            'status' => $isInstantPayment ? 'completed' : 'pending',
        ]);

        if ($isInstantPayment) {
            app(AutoPromotionService::class)->runForBooking($booking);
        }
    }

    private function makePlotBookingCode(int $customerId): string
    {
        $booking = CustomerBooking::findOrFail($customerId);

        if (!$booking->booking_code) {
            $booking->update([
                'booking_code' => 'BK-' . str_pad($booking->id, 6, '0', STR_PAD_LEFT),
            ]);
        }

        $baseCode = $booking->booking_code;
        $existingCodes = PlotSaleDetail::where('customer_booking_id', $customerId)
            ->whereNotNull('booking_code')
            ->pluck('booking_code')
            ->unique()
            ->values();
        $nextNumber = $existingCodes->count() + 1;

        do {
            $code = $baseCode . '-' . str_pad($nextNumber, 2, '0', STR_PAD_LEFT);
            $nextNumber++;
        } while ($existingCodes->contains($code));

        return $code;
    }

    public function deleteBooking($id)
    {
        $customer = CustomerBooking::with([
            'primaryDetail.customerDocument',
            'secondaryDetail.customerDocument',
            'plotSaleDetail',
            'payment',
        ])->findOrFail($id);

        if ($customer->payment) {
            $customer->payment->delete();
        }
        if ($customer->plotSaleDetail) {
            $customer->plotSaleDetail->delete();
        }
        if ($customer->primaryDetail && $customer->primaryDetail->customerDocument) {
            $customer->primaryDetail->customerDocument->delete();
        }
        if ($customer->secondaryDetail && $customer->secondaryDetail->customerDocument) {
            $customer->secondaryDetail->customerDocument->delete();
        }
        if ($customer->secondaryDetail) {
            $customer->secondaryDetail->delete();
        }
        if ($customer->primaryDetail) {
            $customer->primaryDetail->delete();
        }
        $customer->delete();
    }
}
