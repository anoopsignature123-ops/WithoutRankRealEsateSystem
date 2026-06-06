<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateCommissionRequest;
use App\Models\CommissionPayout;
use App\Services\CommissionPayoutService;
use App\Services\ExcelExportService;
use App\Services\PdfExportService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CommissionPayoutController extends Controller
{
    public function __construct(
        private CommissionPayoutService $service,
        private PdfExportService $pdfExportService,
        private ExcelExportService $excelExportService
    ) {}

    public function index(Request $request)
    {
        $fromDate = $this->service->getNextGlobalFromDate();
        $lastGeneratedDate = $this->service->getLastGeneratedToDate();

        $preview = null;
        $warning = null;

        if ($request->filled('to_date')) {
            if ($lastGeneratedDate && $request->to_date <= $lastGeneratedDate) {
                $warning = 'Selected date commission already generated. Please select date from '
                    .date('d M Y', strtotime($fromDate))
                    .' or after.';
            } else {
                $preview = $this->service->previewAllCommission($fromDate, $request->to_date);
            }
        }

        return view('commission-payout.generate', compact(
            'fromDate',
            'lastGeneratedDate',
            'preview',
            'warning'
        ));
    }

    public function store(GenerateCommissionRequest $request)
    {
        $fromDate = $this->service->getNextGlobalFromDate();

        $result = $this->service->generateAllCommission($fromDate, $request->to_date);

        return redirect()
            ->route('generate-commission.index')
            ->with('success', $result['message']);
    }

    public function commissionList(Request $request)
    {
        $commissions = $this->service->getCommissionList($request);

        return view('commission-payout.list', compact('commissions'));
    }

    public function exportCommissionExcel(Request $request)
    {
        $commissions = $this->service->getCommissionList($request);

        return $this->excelExportService->exportDownload(
            data: $commissions,
            fileName: 'commission-ledger',
            headers: $this->commissionHeaders(),
            callbackData: fn ($row) => $this->commissionExportRow($row)
        );
    }

    public function exportCommissionPdf(Request $request)
    {
        $commissions = $this->service->getCommissionList($request);

        return $this->pdfExportService->downloadPdf(
            data: $commissions,
            fileName: 'commission-ledger',
            headers: $this->commissionHeaders(),
            callbackData: fn ($row) => $this->commissionExportRow($row),
            view: 'commission-payout.pdf'
        );
    }

    public function exportSingleExcel(CommissionPayout $commission)
    {
        $commission->load($this->commissionRelations());

        return $this->excelExportService->exportDownload(
            data: collect([$commission]),
            fileName: 'commission-'.$commission->id,
            headers: $this->commissionHeaders(),
            callbackData: fn ($row) => $this->commissionExportRow($row)
        );
    }

    public function exportSinglePdf(CommissionPayout $commission)
    {
        $commission->load($this->commissionRelations());

        return $this->pdfExportService->downloadPdf(
            data: collect([$commission]),
            fileName: 'commission-'.$commission->id,
            headers: $this->commissionHeaders(),
            callbackData: fn ($row) => $this->commissionExportRow($row),
            view: 'commission-payout.pdf'
        );
    }

    private function commissionRelations(): array
    {
        return [
            'associate',
            'sourceAssociate',
            'customerBooking.primaryDetail',
            'plotSaleDetail.project',
            'plotSaleDetail.block',
            'plotSaleDetail.plotDetail',
            'payment',
            'generation',
        ];
    }

    private function commissionHeaders(): array
    {
        return [
            'Generated Date',
            'Period',
            'Associate ID',
            'Associate Name',
            'Source Associate ID',
            'Source Associate Name',
            'Customer',
            'Booking Code',
            'Project',
            'Block',
            'Plot Number',
            'Plot Area',
            'Commission Type',
            'Business Amount',
            'Commission %',
            'Commission Amount',
            'Status',
        ];
    }

    private function commissionExportRow($row): array
    {
        return [
            $row->generated_date
                ? Carbon::parse($row->generated_date)->format('d-m-Y')
                : '-',

            ($row->generation?->from_date
                ? Carbon::parse($row->generation->from_date)->format('d-m-Y')
                : '-')
            .' to '.
            ($row->generation?->to_date
                ? Carbon::parse($row->generation->to_date)->format('d-m-Y')
                : '-'),

            $row->associate?->associate_id ?? '-',
            $row->associate?->associate_name ?? '-',

            $row->sourceAssociate?->associate_id ?? '-',
            $row->sourceAssociate?->associate_name ?? '-',

            $row->customerBooking?->primaryDetail?->name ?? '-',
            $row->customerBooking?->booking_code ?? '-',

            $row->plotSaleDetail?->project?->name ?? '-',
            $row->plotSaleDetail?->block?->block ?? '-',
            $row->plotSaleDetail?->plotDetail?->plot_number ?? '-',

            ($row->plotSaleDetail?->plotDetail?->plot_area ?? '-').' Sqft',

            ucfirst($row->commission_type ?? '-'),

            number_format((float) $row->payment_amount, 2),
            number_format((float) $row->commission_percent, 2).'%',
            number_format((float) $row->commission_amount, 2),

            ucfirst($row->status ?? '-'),
        ];
    }
}