<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ConsolidatedSheetExport implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    private $sheetTitle;
    private $headings;
    private $rows;

    public function __construct(string $sheetTitle, array $headings, Collection $rows)
    {
        $this->sheetTitle = $sheetTitle;
        $this->headings = $headings;
        $this->rows = $rows;
    }

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function title(): string
    {
        return $this->sheetTitle;
    }

    public function styles(Worksheet $sheet): array
    {
        $lastColumn = $sheet->getHighestColumn();
        $lastRow = max(1, $sheet->getHighestRow());

        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill' => [
                'fillType' => 'solid',
                'startColor' => ['argb' => 'FF1F4E78'],
            ],
        ]);
        $sheet->freezePane('A2');
        $sheet->getStyle("A1:{$lastColumn}{$lastRow}")->getAlignment()->setVertical('top');

        return [];
    }
}
