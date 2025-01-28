<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CajaExport implements FromCollection, WithStyles
{

    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {

        array_unshift($this->data, [
            'Fecha Pago', 'Número','Tipo' ,'Concepto', 'Persona','Guias', 'Efectivo', 'Tarjeta', 'Depósito', 'Yape', 'Plin', 'Total', 'Comentario',
        ]);
        return collect($this->data);
    }

    public function headings(): array
    {
        return [

        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheetTitle = "CAJA";

        // Ajustar el título de la hoja con las fechas
        $sheet->setTitle($sheetTitle);

        // Definir el rango de celdas (primera fila de encabezados)
        $lastRow = $sheet->getHighestRow();

        // Establecer ajuste automático para todas las columnas
        foreach (range('A', 'M') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Negrita y centrado para los encabezados
        $sheet->getStyle('A1:M1')->getFont()->setBold(true);
        $sheet->getStyle('A1:M1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Color de fondo para los encabezados
        $sheet->getStyle('A1:M1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $sheet->getStyle('A1:M1')->getFill()->getStartColor()->setARGB('FFCCCCCC'); // Gris claro

        // Estilo de bordes para la tabla
        $sheet->getStyle('A1:M' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        // Ajuste del texto a la izJuierda para los datos (excepto encabezados)
        $sheet->getStyle('A2:M' . $lastRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        // Centrar el contenido de la columna de enlaces "DOCUMENTO"
        $sheet->getStyle('L2:M' . $lastRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    }
}
