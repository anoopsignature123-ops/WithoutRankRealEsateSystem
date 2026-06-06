<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelExportService
{
    public function export($data, $fileName, $headers, $callbackData)
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        $totalColumns = count($headers);
        $lastColumn = Coordinate::stringFromColumnIndex($totalColumns);
        $lastRow = count($data) + 1;

        foreach ($headers as $index => $header) {
            $cell = Coordinate::stringFromColumnIndex($index + 1).'1';
            $sheet->setCellValue($cell, $header);
        }

        $row = 2;

        foreach ($data as $item) {
            $values = $callbackData($item);

            foreach ($values as $index => $value) {
                $cell = Coordinate::stringFromColumnIndex($index + 1).$row;
                $sheet->setCellValue($cell, $value);
            }

            $row++;
        }

        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '198754'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getStyle("A1:{$lastColumn}{$lastRow}")
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        $sheet->getStyle("A2:{$lastColumn}{$lastRow}")
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_TOP)
            ->setWrapText(true);

        $sheet->getStyle("A1:{$lastColumn}{$lastRow}")
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->freezePane('A2');

        foreach (range(1, $totalColumns) as $colIndex) {
            $columnLetter = Coordinate::stringFromColumnIndex($colIndex);
            $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
        }

        foreach (range(1, $lastRow) as $rowNumber) {
            $sheet->getRowDimension($rowNumber)->setRowHeight(-1);
        }

        $writer = new Xlsx($spreadsheet);
        $path = storage_path("app/{$fileName}.xlsx");

        $writer->save($path);

        return response()->download($path)->deleteFileAfterSend(true);
    }

    public function exportDownload($data, $fileName, $headers, $callbackData)
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        $totalColumns = count($headers);
        $lastColumn = Coordinate::stringFromColumnIndex($totalColumns);
        $lastRow = count($data) + 1;

        foreach ($headers as $index => $header) {
            $cell = Coordinate::stringFromColumnIndex($index + 1).'1';
            $sheet->setCellValue($cell, $header);
        }

        $row = 2;
        foreach ($data as $item) {
            $values = $callbackData($item);

            foreach ($values as $index => $value) {
                $cell = Coordinate::stringFromColumnIndex($index + 1).$row;
                $sheet->setCellValue($cell, $value);
            }

            $row++;
        }

        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '198754'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getStyle("A1:{$lastColumn}{$lastRow}")
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        $sheet->getStyle("A2:{$lastColumn}{$lastRow}")
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_TOP)
            ->setWrapText(true);

        $sheet->freezePane('A2');

        foreach (range(1, $totalColumns) as $colIndex) {
            $columnLetter = Coordinate::stringFromColumnIndex($colIndex);
            $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
        }

        foreach (range(1, $lastRow) as $rowNumber) {
            $sheet->getRowDimension($rowNumber)->setRowHeight(-1);
        }

        $sheet->getStyle("A1:{$lastColumn}{$lastRow}")
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER);

        $writer = new Xlsx($spreadsheet);
        $path = storage_path("app/{$fileName}.xlsx");

        $writer->save($path);

        return response()->download($path)->deleteFileAfterSend(true);
    }
}
