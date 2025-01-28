<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReceptionsExport implements FromCollection, WithStyles
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
    
        // Agregar los encabezados después del título (segunda fila)
        array_unshift($this->data, [
            'COD RECEPCION',
            'FECHA SOLIC.',
           
            'REMITENTE',
            'DESTINATARIO',

            'ORIGEN',
            'DESTINO',
            'CLIENTE PAGA',
            'DOCUMENTOS ANEXOS',
            'FLETE',
            'DEUDA',
            'CARGA',
            'PESO',
            'GUIA',
            'DOC. VENTA',
            'ESTADO RECEPCIÓN',
            'USUARIO',
        ]);
    
        // Retornar la colección con los datos
        return collect($this->data);
    }
    public function styles(Worksheet $sheet)
    {
        $sheetTitle = "{$this->fechaInicio}_AL_{$this->fechaFin}";
        
        // Ajustar el título de la hoja con las fechas
        $sheet->setTitle($sheetTitle);
    
        // Definir el rango de celdas (primera fila de encabezados)
        $lastRow = $sheet->getHighestRow();
    
        // Establecer ajuste automático para todas las columnas, excepto H y K
        foreach (range('A', 'O') as $column) {
            if (!in_array($column, ['H', 'K'])) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
        }
    
        // Negrita y centrado para los encabezados
        $sheet->getStyle('A1:O1')->getFont()->setBold(true);
        $sheet->getStyle('A1:O1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    
        // Color de fondo para los encabezados
        $sheet->getStyle('A1:O1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $sheet->getStyle('A1:O1')->getFill()->getStartColor()->setARGB('FFCCCCCC'); // Gris claro
    
        // Estilo de bordes para la tabla
        $sheet->getStyle('A1:O' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);
    
        // Ajuste del texto a la izquierda para los datos (excepto encabezados)
        $sheet->getStyle('A2:O' . $lastRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
    
        // Centrar el contenido de la columna de enlaces "DOCUMENTO"
        $sheet->getStyle('O2:O' . $lastRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    }
    
}
