<?php

namespace App\Exports;

use App\Models\CarrierGuide;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\FromCollection;

class GuidesExport implements FromCollection, WithStyles
{
    protected $data;
    protected $fechaInicio;
    protected $fechaFin;

    public function __construct($data, $fechaInicio, $fechaFin)
    {
        $this->data = $data;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
    }

    public function collection()
    {
        // Encabezados
        array_unshift($this->data, [
            'N°',
            'CARGA MATERIAL',
            'REMITENTE',
            'REM. RUC/DNI',
            'DESTINATARIO',
            'DEST. RUC/DNI',
            'PUNTO PARTIDA',
            'PUNTO LLEGADA',
            'DOCUMENTOS ANEXOS',
            'GUÍA GRT',
            'TOTAL PESO',
            'TOTAL FLETE',
            'SALDO',
            'COND. PAGO',
            'ESTADO ENTREGA',
            'ESTADO FACTURACION',

            'FECHA DE RECEPCION DE GRT',
            'FECHA CARGO',
            'FECHA EST. FACTURACIÓN',

            'CONDUCTOR 1',
            'LICENCIA 1',
            'CONDUCTOR 2',
            'LICENCIA 2',
            'PLACA 1',
            'PLACA 2',
            'MTC 1',
            'MTC 2',
        ]);

        return collect($this->data);
    }

    public function styles(Worksheet $sheet)
    {
        $sheetTitle = "{$this->fechaInicio}_AL_{$this->fechaFin}";
        $sheet->setTitle($sheetTitle);

        // Última fila de datos
        $lastRow = $sheet->getHighestRow();

        // Ahora el rango llega hasta 'Z' (3 columnas más que antes)
        $columnRange = 'A:Z';

        // Ajuste de ancho automático
        foreach (range('A', 'Z') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Estilo para los encabezados
        $sheet->getStyle('A1:Z1')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFCCCCCC'], // Gris claro
            ],
        ]);

        // Estilo de bordes para toda la tabla
        $sheet->getStyle("A1:Z{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        // Alineación de datos
        $sheet->getStyle("A2:Z{$lastRow}")
            ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        // Centrar solo la columna de GUÍA GRT (que ahora es J)
        $sheet->getStyle("J2:J{$lastRow}")
            ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    }
}
