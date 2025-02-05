<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class KardexProductSheet implements FromCollection, WithHeadings, WithMapping
{
    protected $registros;
    protected $productId;

    public function __construct($registros, $productId)
    {
        $this->registros = $registros;
        $this->productId = $productId;
    }

    public function collection()
    {
        return $this->registros;
    }

    public function headings(): array
    {
        return [
            ['REPORTE DE KARDEX'],
            ['Producto ID: ' . $this->productId],
            ['Fecha de Generación:', now()->format('d/m/Y')],
            [],
            [
                'Fecha Movimiento',
                'Tipo Movimiento',
                'Producto',
                'Documento',
                'Cantidad Entrada',
                'Cantidad Salida',
                'Cantidad Saldo',
                'Comentario',
            ],
        ];
    }

    public function map($row): array
    {
        return [
            (string) $row['movement_date'],
            (string) $row['type'],
            (string) $row['concept'],
            (string) $row['document'],
            (string) $row['quantity'], // Deberías asegurarte de que esto sea correcto
            (string) $row['quantity'], // Deberías asegurarte de que esto sea correcto
            (string) $row['saldo'],    // Asegúrate de que este valor está disponible
            (string) $row['comment'],
        ];
    }
}
