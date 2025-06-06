<?php
namespace App\Exports\guides;

use App\Http\Resources\CarrierGuideIntegradoResource;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GuideSheetExport implements FromCollection, WithTitle, WithStyles
{
    protected Collection $data;
    protected string $serie;

    public function __construct(Collection $data, string $serie)
    {
        $this->data  = $data;
        $this->serie = $serie;
    }

    public function collection(): Collection
    {
        if ($this->data->isEmpty()) {
            return collect();
        }

        // Ordenar y mapear en una sola operación para reducir pasos innecesarios
        $mapped = $this->data
            ->sortByDesc('id')
            ->map(fn($item) => (new CarrierGuideIntegradoResource($item))->toArray())
            ->values(); // Reinicia los índices para evitar huecos

        // Obtener los encabezados desde el primer elemento ya transformado
        $headers = array_keys($mapped->first());

        // Concatenar encabezados con los valores mapeados
        return collect([$headers])->concat(
            $mapped->map(fn($row) => array_values($row))
        );
    }

    public function title(): string
    {
        return $this->serie;
    }

    public function styles(Worksheet $sheet): array
    {
        $lastCol = $sheet->getHighestColumn();
        $lastRow = $sheet->getHighestRow();

        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font'      => ['bold' => true],
            'alignment' => ['horizontal' => 'center'],
            'fill'      => [
                'fillType'   => 'solid',
                'startColor' => ['argb' => 'FFEEEEEE'],
            ],
        ]);

        $sheet->getStyle("A1:{$lastCol}{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => 'thin',
                    'color'       => ['argb' => 'FF999999'],
                ],
            ],
        ]);

        return [];
    }
}
