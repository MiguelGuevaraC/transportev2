<?php
namespace App\Exports;

use App\Services\KardexReportService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KardexExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles
{
    protected $product_id;
    protected $from;
    protected $to;

    protected $branch_id;

    public function __construct($product_id = [], $from = null, $to = null, $branch_id = null)
    {
        $this->product_id = is_array($product_id) ? $product_id : ($product_id == "null" ? "null" : [$product_id]);
        $this->from       = $from;
        $this->to         = $to;
        $this->branch_id  = $branch_id;
    }

    public function collection()
    {
        $svc = new KardexReportService(
            $this->product_id,
            $this->from,
            $this->to,
            $this->branch_id ? (int) $this->branch_id : null
        );

        return $svc->toFlatCollection();
    }

    public function headings(): array
    {
        return [
            ['REPORTE DE KARDEX'],
            ['Fecha de Generación:', now()->format('d/m/Y')],
            [],
        ];
    }

    public function map($row): array
    {

        if (isset($row['is_header']) && $row['is_header']) {
            return [
                strtoupper($row['movement_date']),
                $row['type'],
                $row['concept'],
                $row['document'],
                $row['num_anexo'],
                $row['person'],
                $row['distribuidor'],

                $row['quantity'],
                $row['saldo'],
                $row['comment'],
            ];
        }

        return [
            (string) $row['movement_date'],
            (string) $row['type'],
            (string) $row['concept'],
            (string) $row['document'],
            (string) $row['num_anexo'],
            (string) $row['person'],
            (string) $row['distribuidor'],
            (string) $row['quantity'],
            (string) $row['saldo'],
            (string) $row['comment'],
        ];
    }

    public function title(): string
    {
        return 'Kardex';
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();

        // Iterar por cada fila para aplicar los estilos dinámicamente
        foreach ($sheet->getRowIterator(1, $highestRow) as $row) {
            $rowIndex = $row->getRowIndex();

            // Obtener los valores de las celdas A a G de la fila
            $cellA = trim($sheet->getCell("A{$rowIndex}")->getValue());
            $cellB = trim($sheet->getCell("B{$rowIndex}")->getValue());
            $cellC = trim($sheet->getCell("C{$rowIndex}")->getValue());
            $cellD = trim($sheet->getCell("D{$rowIndex}")->getValue());
            $cellE = trim($sheet->getCell("E{$rowIndex}")->getValue());
            $cellF = trim($sheet->getCell("F{$rowIndex}")->getValue());
            $cellG = trim($sheet->getCell("G{$rowIndex}")->getValue());
            $cellH = trim($sheet->getCell("H{$rowIndex}")->getValue());
            $cellI = trim($sheet->getCell("I{$rowIndex}")->getValue());
            $cellJ = trim($sheet->getCell("J{$rowIndex}")->getValue());

            // 1. Estilo para la cabecera del producto (ej.: "PRODUCTO 01", "PRODUCTO 03", etc.)
            // Se asume que esa fila tiene contenido solo en la columna A y las demás vacías.
            if ($cellA !== '' && $cellB === '' && $cellC === '' && $cellD === '' && $cellE === '' && $cellF === ''
                && $cellG === '' && $cellH === '' && $cellI === '' && $cellJ === '') {
                // Combinar celdas de A a G en esa fila
                $sheet->mergeCells("A{$rowIndex}:J{$rowIndex}");
                // Aplicar estilo: fondo blanco, borde negro, texto centrado y en negrita
                $sheet->getStyle("A{$rowIndex}")->applyFromArray([
                    'fill'      => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D9D9D9'],
                    ],
                    'borders'   => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['rgb' => '000000'], // Color negro para los bordes
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'font'      => [
                        'bold' => true,
                    ],
                ]);
            }

            // 2. Estilo para el cabezal de la tabla (encabezados de columna)
            // Se asume que la fila cuyo valor en A es "Fecha Movimiento" es el cabezal.
            if (strcasecmp($cellA, 'Fecha Movimiento') === 0) {
                $sheet->getStyle("A{$rowIndex}:J{$rowIndex}")->applyFromArray([
                    'fill'      => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D9D9D9'], // Color de fondo para el encabezado (gris claro)
                    ],
                    'borders'   => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'font'      => [
                        'bold' => true,
                    ],
                ]);
            }

            // 3. Estilos para los registros de movimientos
            // a) Filas de SALIDA (fondo rojo suave)
            if ($cellB === 'SALIDA') {
                $sheet->getStyle("A{$rowIndex}:J{$rowIndex}")->applyFromArray([
                    'fill'      => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FF9999'],
                    ],
                    'borders'   => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ]);
            }

            // b) Filas de ENTRADA y SALDO INICIAL (se tratará SALDO INICIAL igual que ENTRADA, fondo verde suave)
            if ($cellB === 'ENTRADA' || $cellB === 'SALDO INICIAL') {
                $sheet->getStyle("A{$rowIndex}:J{$rowIndex}")->applyFromArray([
                    'fill'      => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '99FF99'],
                    ],
                    'borders'   => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ]);
            }
        }

        return [];
    }

}
