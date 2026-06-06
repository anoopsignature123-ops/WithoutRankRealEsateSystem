<?php

namespace App\Http\Controllers\AssociatePanel;
use App\Http\Controllers\Controller;
use App\Models\CommissionPayout;
use App\Services\ExcelExportService;
use App\Services\PdfExportService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AssociateCommissionController extends Controller
{
    public function __construct(
        private PdfExportService $pdfExportService,
        private ExcelExportService $excelExportService
    ) {}

    public function index(Request $request)
    {
        $associate = auth('associate')->user();

        $commissions = $this->query($request, $associate->id)->get();

        return view('associate-panel.payout-details.index', compact('commissions', 'associate'));
    }

    public function exportExcel(Request $request)
    {
        $associate = auth('associate')->user();

        $commissions = $this->query($request, $associate->id)->get();

        return $this->excelExportService->export(
            data: $commissions,
            fileName: 'my-payout-details',
            headers: $this->headers(),
            callbackData: fn ($row) => $this->row($row)
        );
    }

    public function exportPdf(Request $request)
    {
        $associate = auth('associate')->user();

        $commissions = $this->query($request, $associate->id)->get();

        return $this->pdfExportService->downloadPdf(
            data: $commissions,
            fileName: 'my-payout-details',
            headers: $this->headers(),
            callbackData: fn ($row) => $this->row($row),
            view: 'associate-panel.payout-details.pdf'
        );
    }

    public function exportSingleExcel(CommissionPayout $commission)
    {
        $associate = auth('associate')->user();

        abort_if((int) $commission->associate_id !== (int) $associate->id, 403);

        $commission->load($this->relations());

        return $this->excelExportService->export(
            data: collect([$commission]),
            fileName: 'my-payout-' . $commission->id,
            headers: $this->headers(),
            callbackData: fn ($row) => $this->row($row)
        );
    }

    public function exportSinglePdf(CommissionPayout $commission)
    {
        $associate = auth('associate')->user();

        abort_if((int) $commission->associate_id !== (int) $associate->id, 403);

        $commission->load($this->relations());

        return $this->pdfExportService->downloadPdf(
            data: collect([$commission]),
            fileName: 'my-payout-' . $commission->id,
            headers: $this->headers(),
            callbackData: fn ($row) => $this->row($row),
            view: 'associate-panel.payout-details.pdf'
        );
    }

    private function query(Request $request, int $associateId)
    {
        $query = CommissionPayout::with($this->relations())
            ->where('associate_id', $associateId)
            ->latest();

        if ($request->commission_type) {
            $query->where('commission_type', $request->commission_type);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->from_date && $request->to_date) {
            $query->whereBetween('generated_date', [
                Carbon::parse($request->from_date)->startOfDay(),
                Carbon::parse($request->to_date)->endOfDay(),
            ]);
        } elseif ($request->from_date) {
            $query->whereDate('generated_date', '>=', $request->from_date);
        } elseif ($request->to_date) {
            $query->whereDate('generated_date', '<=', $request->to_date);
        }

        return $query;
    }

    private function relations(): array
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

    private function headers(): array
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

    private function row($row): array
    {
        return [
            $row->generated_date ? Carbon::parse($row->generated_date)->format('d-m-Y') : '-',

            ($row->generation?->from_date ? Carbon::parse($row->generation->from_date)->format('d-m-Y') : '-')
            . ' to ' .
            ($row->generation?->to_date ? Carbon::parse($row->generation->to_date)->format('d-m-Y') : '-'),

            $row->associate?->associate_id ?? '-',
            $row->associate?->associate_name ?? '-',
            $row->sourceAssociate?->associate_id ?? '-',
            $row->sourceAssociate?->associate_name ?? '-',
            $row->customerBooking?->primaryDetail?->name ?? '-',
            $row->customerBooking?->booking_code ?? '-',
            $row->plotSaleDetail?->project?->name ?? '-',
            $row->plotSaleDetail?->block?->block ?? '-',
            $row->plotSaleDetail?->plotDetail?->plot_number ?? '-',
            ($row->plotSaleDetail?->plotDetail?->plot_area ?? '-') . ' Sqft',
            ucfirst($row->commission_type ?? '-'),
            number_format((float) $row->payment_amount, 2),
            number_format((float) $row->commission_percent, 2) . '%',
            number_format((float) $row->commission_amount, 2),
            ucfirst($row->status ?? '-'),
        ];
    }
}