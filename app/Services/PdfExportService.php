<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;

class PdfExportService
{
    public function export($data, $fileName, $headers, $callbackData
    ) {
        $rows = [];
        foreach ($data as $item) {
            $rows[] = $callbackData($item);
        }
        // $pdf = Pdf::loadView('exports.common-pdf', ['headers' => $headers, 'rows' => $rows]);
        $pdf = Pdf::loadView('exports.project-manipulation-pdf',
            [
                'plots' => $data,
            ]
        );
        return $pdf->download($fileName.'.pdf');
    }

    public function downloadPdf($data, $fileName, $headers, $callbackData, $view = 'exports.common-pdf')
    {
        $rows = [];

        foreach ($data as $item) {
            $rows[] = $callbackData($item);
        }

        $pdf = Pdf::loadView($view, [
            'headers'     => $headers,
            'rows'        => $rows,
            'data'        => $data,
            'commissions' => $data,
            'title'       => ucwords(str_replace('-', ' ', $fileName)),
        ])
        ->setPaper('a4', 'landscape')
        ->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
        ]);

        return $pdf->download($fileName . '.pdf');
    }
}