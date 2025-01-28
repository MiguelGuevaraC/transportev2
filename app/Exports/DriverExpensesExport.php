<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DriverExpensesExport implements FromCollection, WithStyles
{
    protected $data1;
    protected $data2;
    protected $viaje;

    public function __construct($data1, $data2, $viaje)
    {
        $this->data1 = $data1; // Datos para la primera tabla
        $this->data2 = $data2; // Datos para la segunda tabla
        $this->viaje = $viaje;
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $result = [];

        // Primera tabla: Título, encabezados y datos
        $result[] = ['VIAJE']; // Título de la primera tabla
        $result[] = [
            'NUMERO',
            'F. VIAJE',
            'INGRESOS VIAJE',
            'EGRESOS VIAJE',
            'SALDO VIAJE',
            'ORIGEN',
            'DESTINO',
            'TRACTO',
            'CARRETA',
            'TOTAL PESO',
            'ESTADO',
            'ESTADO DE GASTO',
            'LIQUIDACIÓN',
        ];
        foreach ($this->data1 as $row) {
            $result[] = $row;
        }

        // Separador entre tablas
        $result[] = [''];
        $result[] = ['REGISTROS GASTOS']; // Título de la segunda tabla

        // Segunda tabla: Encabezados y datos
        $result[] = [
            'N°',
            'CONCEPTO GASTO',
            'CANTIDAD',
            'LUGAR',
            'KM',
            'GALONES',
            'NRO OPERACION',
            'IGV',
            'EXONERADO',
            'GRAVADO',
            'BANCO',
            'PROVEEDOR',
            'COMENTARIO',
            'TIPO MOVIMIENTO',
            'TIPO DE PAGO',
            'FECHA',
            'TOTAL',
        ];
        foreach ($this->data2 as $row) {
            $result[] = $row;
        }

        // Retornar la colección con las dos tablas
        return collect($result);
    }
    public function styles(Worksheet $sheet)
    {
        // Título de la hoja con el nombre del viaje
        $sheetTitle = "{$this->viaje}";
        $sheet->setTitle($sheetTitle);

        // Obtener la última fila
        $lastRow = $sheet->getHighestRow();

        // Ajustar automáticamente las columnas para ambas tablas (A hasta M para la primera, y hasta G para la segunda)
        foreach (range('A', 'Q') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Estilos para el encabezado de la primera tabla (fila 2)
        $sheet->getStyle('A2:Q2')->getFont()->setBold(true);
        $sheet->getStyle('A2:Q2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2:Q2')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $sheet->getStyle('A2:Q2')->getFill()->getStartColor()->setARGB('FFCCCCCC'); // Gris claro

        // Estilos para el encabezado de la segunda tabla
        // Buscar la fila en la que comienza la segunda tabla
        $secondTableRow = count($this->data1) + 5; // Suponiendo 3 filas de espacio
        $sheet->getStyle("A{$secondTableRow}:Q{$secondTableRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$secondTableRow}:Q{$secondTableRow}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A{$secondTableRow}:Q{$secondTableRow}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $sheet->getStyle("A{$secondTableRow}:Q{$secondTableRow}")->getFill()->getStartColor()->setARGB('FFCCCCCC'); // Gris claro

        // Bordes para ambas tablas
        $sheet->getStyle('A2:Q' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        // Ajustar el texto a la izquierda para los datos
        $sheet->getStyle('A3:Q' . $lastRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        // Centrar columnas específicas en ambas tablas
        $sheet->getStyle('A3:Q' . $lastRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    }
}
