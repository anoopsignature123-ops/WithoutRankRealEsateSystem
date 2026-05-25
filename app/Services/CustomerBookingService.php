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
use Illuminate\Support\Str;

class CustomerBookingService
{
    public function getAll()
    {
        return CustomerBooking::with([
            'primaryDetail',
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
            'payment',
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
        if (! $customerId) {
            $lastId = CustomerBooking::max('id') + 1;
            $customerCode = 'CUST-'.str_pad($lastId, 4, '0', STR_PAD_LEFT);
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
            'telephone_no' => $data['telephone_no'] ?? null,
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
                'telephone_no' => $data['secondary_telephone_no'] ?? null,
                'email' => $data['secondary_email'] ?? null,
            ]);
        } else {
            SecondaryDetail::where('customer_booking_id', $customerId)->delete();
        }

        CustomerBooking::where('id', $customerId)->update(['current_step' => 3]);
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
        $oldPlotSale = PlotSaleDetail::where('customer_booking_id', $customerId)
            ->latest()
            ->first();

        // Same plot already selected → new record mat banao
        if ($oldPlotSale && $oldPlotSale->plot_detail_id == ($data['plot_detail_id'] ?? null)) {
            CustomerBooking::where('id', $customerId)->update(['current_step' => 5]);

            return $oldPlotSale;
        }

        // New plot selected → new booking create karo
        $plotSale = PlotSaleDetail::create([
            'customer_booking_id' => $customerId,
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
        $plotSaleId = $data['plot_sale_detail_id'];
        $transactionNumber = $data['transaction_number'] ?? null;

        if (! $transactionNumber) {
            $transactionNumber = strtoupper($paymentMode ?: 'PAY').'-'.time();
        }
        $receiptNumber = $data['receipt_number'] ?? 'REC-'.Str::upper(Str::random(8));
        $paymentStatus = 'hold';
        if (in_array($paymentMode, ['cash', 'card'], true)) {
            $paymentStatus = 'booked';
        }
        $oldPayment = CustomerPayment::where('customer_booking_id', $customerId)
            ->where('plot_sale_detail_id', $plotSaleId)
            ->first();

        if (! $oldPayment) {
            CustomerPayment::create([
                'plan_type' => $planType,
                'booking_amount' => $data['booking_amount'] ?? 0,
                'due_amount' => $data['due_amount'] ?? 0,
                'net_payable_amount' => $data['net_payable_amount'] ?? 0,
                'emi_months' => $data['emi_months'] ?? null,
                'emi_date' => now() ?? null,
                'after_booking_payable_amount' => $data['after_booking_payable_amount'] ?? null,
                'remark' => $data['remark'] ?? null,
                'payment_mode' => $paymentMode,
                'account_number' => $data['account_number'] ?? null,
                'bank_name' => $data['bank_name'] ?? null,
                'branch_name' => $data['branch_name'] ?? null,
                'cheque_number' => $data['cheque_number'] ?? null,
                'cheque_date' => $data['cheque_date'] ?? null,
                'dd_number' => $data['dd_number'] ?? null,
                'transaction_number' => $transactionNumber,
                'payment_status' => $paymentStatus,
                'receipt_number' => $receiptNumber,
                'customer_booking_id' => $customerId,
                'plot_sale_detail_id' => $plotSaleId,
                'transaction_category' => 'booking_fee',
            ]);
        }

        // Plot lock for all payment types
        $plotSale = PlotSaleDetail::find($plotSaleId);
        if ($plotSale && $plotSale->plot_detail_id) {
            PlotDetail::where('id', $plotSale->plot_detail_id)->update(['status' => 'booked']);
        }

        // Booking code always generate once
        $booking = CustomerBooking::find($customerId);
        if ($booking && ! $booking->booking_code) {
            $booking->update([
                'booking_code' => 'BK-'.str_pad($booking->id, 6, '0', STR_PAD_LEFT),
            ]);
        }

        // Booking status update
        CustomerBooking::where('id', $customerId)->update([
            'current_step' => 6,
            'status' => $paymentStatus == 'booked' ? 'completed' : 'pending',
        ]);
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
