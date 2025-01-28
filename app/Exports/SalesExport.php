<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesExport implements WithMultipleSheets
{
    protected $dataSinNotaCredito;
    protected $dataConNotaCredito;
    protected $fechaInicio;
    protected $fechaFin;

    public function __construct($dataSinNotaCredito, $dataConNotaCredito, $fechaInicio, $fechaFin)
    {
        $this->dataSinNotaCredito = $dataSinNotaCredito;
        $this->dataConNotaCredito = $dataConNotaCredito;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
    }

    public function sheets(): array
    {
        return [
            new VentasSheet($this->dataSinNotaCredito, $this->generateSheetTitle('Ventas')),
            new VentasSheet($this->dataConNotaCredito, $this->generateSheetTitle('Ventas con NC')), // Título modificado aquí
        ];
    }

    protected function generateSheetTitle($baseTitle)
    {
        // Construir el título con las fechas
        $title = "{$baseTitle} {$this->fechaInicio} a {$this->fechaFin}";

        // Truncar el título a 31 caracteres si es necesario
        return substr($title, 0, 31);
    }
}

class VentasSheet implements FromCollection, WithStyles
{
    protected $data;
    protected $sheetTitle;

    public function __construct($data, $sheetTitle)
    {
        $this->data = $data;
        $this->sheetTitle = $sheetTitle;
    }

    public function collection()
    {
        // Convertir la colección en un array si es una colección
        $dataArray = is_array($this->data) ? $this->data : $this->data->toArray();

        // Agregar los encabezados a la primera fila
        if (strpos($this->sheetTitle, 'NC') !== false) {
            array_unshift($dataArray, [
                'FECHA DE EMISION NOTA CREDITO',
                'NUMERO NOTA CREDITO',
                'RAZON',
                'FECHA DE EMISION VENTA',
                'NUMERO VENTA',
                'DNI/RUC',
                'RAZON SOCIAL',
                'AFECTO S/',
                'INAFECTO S/',
                'IGV S/',
                'TOTAL S/',
                'DETRACCION',
                'SALDO NETO',
                'ESTADO',
                'USUARIO',
            ]);
        } else { // Para 'Ventas'
            array_unshift($dataArray, [
                'FECHA DE EMISION',
                'NUMERO',
                'DNI/RUC',
                'RAZON SOCIAL',
                'AFECTO S/',
                'INAFECTO S/',
                'IGV S/',
                'TOTAL S/',
                'DETRACCION',
                'SALDO NETO',
                'ESTADO',
                'USUARIO',
            ]);
        }

        // Retornar la colección con los datos modificados
        return collect($dataArray);
    }

    public function styles(Worksheet $sheet)
    {
        // Ajustar el título de la hoja
        $sheet->setTitle($this->sheetTitle);

        // Estilos para las columnas
        foreach (range('A', 'P') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Hacer los encabezados en negrita
        $sheet->getStyle('A1:P1')->getFont()->setBold(true);

        // Alinear el contenido a la izquierda
        $sheet->getStyle('A1:P' . $sheet->getHighestRow())->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
    }
}
