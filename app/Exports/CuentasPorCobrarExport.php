<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CuentasPorCobrarExport implements FromCollection, WithStyles
{
    protected $data;
    protected $fechaInicio;
    protected $fechaFin;

    public function __construct($data, $fechaInicio, $fechaFin)
    {
        $this->data        = $data;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin    = $fechaFin;
    }

    public function collection()
    {

        // Agregar los encabezados después del título (segunda fila)
        array_unshift($this->data, [
            'Item', 'RUC / DNI', 'Razón Social', 'Fecha de Emision',
            'Fecha de Vencimiento', 'Documento',
            'Total', 'Total Deuda', 'Detraccion',
            'Saldo Neto', 'Obs',
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

        // Establecer ajuste automático para todas las columnas
        foreach (range('A', 'K') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Negrita y centrado para los encabezados
        $sheet->getStyle('A1:K1')->getFont()->setBold(true);
        $sheet->getStyle('A1:K1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Color negro para los textos de los encabezados
        $sheet->getStyle('A1:K1')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLACK);

        // Color de fondo para los encabezados (puedes eliminar esto si no lo necesitas)
        $sheet->getStyle('A1:K1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
        $sheet->getStyle('A1:K1')->getFill()->getStartColor()->setARGB('FF000000'); // Fondo blanco
        $sheet->getStyle('A1:K1')->getFont()->getColor()->setARGB('FFFFFFFF');      // Letra blanca

        // Estilo de bordes para la tabla
        $sheet->getStyle('A1:K' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color'       => ['argb' => 'FF000000'], // Color negro para los bordes
                ],
            ],
        ]);

        // Alineación a la izquierda para los datos (excepto encabezados)
        $sheet->getStyle('A1:K' . $lastRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        // Centrar el contenido de la columna de enlaces "DOCUMENTO"
        $sheet->getStyle('A1:K' . $lastRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    }

}
