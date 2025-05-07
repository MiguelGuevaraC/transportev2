<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExcelExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $data;
    protected $columns;
    protected $lastColumn;
    protected $sumColumns;

    public function __construct($data, $columns, $sumColumns = [])
    {
        $this->data       = collect(($data instanceof \Illuminate\Http\JsonResponse ? $data->getData(true) : $data) ?? []);
        $this->columns    = $columns;
        $this->sumColumns = $sumColumns;
        $this->lastColumn = $this->getColumnLetter(count($this->columns));
    }

    public function collection()
    {
        if ($this->data->isEmpty()) {
            return collect([]);
        }


        $dataArray = $this->data->map(fn($row) =>
            collect($this->columns)->mapWithKeys(fn($columnPath, $columnName) => [
                $columnName => is_array($columnPath)
                    ? implode(' ', array_filter(array_map(fn($key) => data_get($row, $key, ''), $columnPath)))
                    : strval(data_get($row, $columnPath, ''))
                    
            ])->toArray()
        )->toArray();
     

        if (!empty($this->sumColumns)) {
            $totalRow = collect($this->columns)->mapWithKeys(fn($_, $key) => [$key => ''])->toArray();
            $totalRow[array_key_first($this->columns)] = 'TOTAL';

            foreach ($this->sumColumns as $sumColumn) {
                if (isset($totalRow[$sumColumn])) {
                    $totalRow[$sumColumn] = number_format(collect($dataArray)->sum(fn($row) => (float) ($row[$sumColumn] ?? 0)), 2, '.', '');
                }
            }

            $dataArray[] = $totalRow;
        }

        return collect($dataArray);
    }

    public function headings(): array
    {
        return array_keys($this->columns);
    }

    public function map($movement): array
    {
        
        return $movement;
    }

    public function styles(Worksheet $sheet)
    {
        $rowCount = $this->data->count() + 1 + (!empty($this->sumColumns) ? 1 : 0);
        $range    = "A1:{$this->lastColumn}{$rowCount}";

        $sheet->getStyle("A1:{$this->lastColumn}1")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '000000']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        foreach (range('A', $this->lastColumn) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $sheet->getStyle("{$col}2:{$col}{$rowCount}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $sheet->getStyle($range)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
        ]);

        if (!empty($this->sumColumns)) {
            $sheet->getStyle("A{$rowCount}:{$this->lastColumn}{$rowCount}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => '000000']],
            ]);
        }
    }

    private function getColumnLetter($index): string
    {
        $letters = '';
        while ($index > 0) {
            $index--;
            $letters = chr(65 + ($index % 26)) . $letters;
            $index   = intval($index / 26);
        }
        return $letters;
    }
}
