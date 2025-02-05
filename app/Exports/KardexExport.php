<?php
namespace App\Exports;

use App\Models\CargaDocument;
use App\Models\DetailReception;
use App\Models\Product;
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

    public function __construct($product_id = null, $from = null, $to = null)
    {
        $this->product_id = $product_id == "null" ? null : $product_id;
        $this->from       = $from;
        $this->to         = $to;
    }

    public function collection()
    {

        $products = $this->product_id != null
        ? CargaDocument::where('product_id', $this->product_id)->pluck('product_id')->unique()
        : CargaDocument::pluck('product_id')->unique();

        $finalCollection = new Collection();

        foreach ($products as $product_id) {
            $queryCarga = CargaDocument::query()->whereNull('deleted_at');
            $queryRecep = DetailReception::query()->whereNull('deleted_at');

            if ($this->product_id) {
                $queryCarga->where('product_id', $this->product_id);
                $queryRecep->where('product_id', $this->product_id);
            } else {
                $queryRecep->whereNotNull('product_id');
            }

            if ($this->from) {
                $toDate = $this->to ?? now();
                $queryCarga->whereBetween('movement_date', [$this->from, $toDate]);
                $queryRecep->whereHas('reception.firstCarrierGuide', function ($query) use ($toDate) {
                    $query->whereBetween('transferStartDate', [$this->from, $toDate]);
                });
            }
            $saldoInicial = $this->getStockBefore($product_id);

            // Encabezado de producto
            $finalCollection->push([
                'is_header'     => true,
                'movement_date' => Product::find($product_id)->description ?? 'SIN NOMBRE',
                'type'          => '',
                'concept'       => '',
                'document'      => '',
                'quantity'      => '',
                'saldo'         => '',
                'comment'       => '',
            ]);

            // Encabezado de la tabla
            $finalCollection->push([
                'is_header'     => true,
                'movement_date' => 'Fecha Movimiento',
                'type'          => 'Tipo Movimiento',
                'concept'       => 'Concepto',
                'document'      => 'Documento',
                'quantity'      => 'Cantidad',
                'saldo'         => 'Saldo',
                'comment'       => 'Comentario',
            ]);

            // Registro del saldo inicial
            $finalCollection->push([
                'movement_date' => $this->from ?? now(),
                'type'          => 'SALDO INICIAL',
                'concept'       => 'Stock acumulado hasta ' . ($this->from ?? 'Hoy'),
                'document'      => '0000-0000000',
                'quantity'      => (string) 0,
                'saldo'         => (string) $saldoInicial,
                'comment'       => '-',
            ]);
            // Obtener registros de movimientos
            $cargaDocuments = $queryCarga->where('product_id', $product_id)->orderBy('movement_date', 'asc')->get()->map(function ($doc) {
                return [
                    'movement_date' => $doc->movement_date,
                    'type'          => $doc->movement_type,
                    'concept'       => 'DOCUMENTO DE CARGA',
                    'document'      => 'D' . str_pad($doc->product_id, 3, '0', STR_PAD_LEFT) . '-' . str_pad($doc->id, 8, '0', STR_PAD_LEFT),
                    'quantity'      => $doc->quantity,
                    'saldo'         => null,
                    'comment'       => $doc->comment ?? "-",
                ];
            });

            $detailReceptions = $queryRecep->where('product_id', $product_id)->get()->map(function ($detail) {
                return [
                    'movement_date' => $detail->reception->firstCarrierGuide->transferStartDate ?? now(),
                    'type'          => 'SALIDA',
                    'concept'       => 'GUIA TRANSPORTE',
                    'document'      => $detail->reception->firstCarrierGuide->numero ?? 'N/A',
                    'quantity'      => $detail->cant,
                    'saldo'         => null,
                    'comment'       => '-',
                ];
            })->sortBy('movement_date')->values();

            // Calcular saldo acumulado correctamente
            $saldo   = $saldoInicial;
            $records = (new Collection(array_merge($cargaDocuments->toArray(), $detailReceptions->toArray())))
                ->sortBy('movement_date')
                ->map(function ($row) use (&$saldo) {
                    $cantidad     = $row['quantity'];
                    $saldo        = ($row['type'] === 'ENTRADA') ? $saldo + $cantidad : $saldo - $cantidad;
                    $row['saldo'] = $saldo;
                    return $row;
                });

            $emptyRows = collect([
                ['movement_date' => '',
                    'type'           => '',
                    'concept'        => '',
                    'document'       => '',
                    'quantity'       => '',
                    'saldo'          => '',
                    'comment'        => ''],
            ]);

            // Agregar los registros a la colección final
            $finalCollection = $finalCollection->merge($records)->merge($emptyRows);
        }

        return $finalCollection;
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
            (string) $row['quantity'],
            (string) $row['saldo'],
            (string) $row['comment'],
        ];
    }

    private function getStockBefore($productId)
    {
        return CargaDocument::whereNull('deleted_at')
            ->where('product_id', $productId)
            ->where('movement_date', '<', $this->from)
            ->sum('quantity');
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
            
            // 1. Estilo para la cabecera del producto (ej.: "PRODUCTO 01", "PRODUCTO 03", etc.)
            // Se asume que esa fila tiene contenido solo en la columna A y las demás vacías.
            if ($cellA !== '' && $cellB === '' && $cellC === '' && $cellD === '' && $cellE === '' && $cellF === '' && $cellG === '') {
                // Combinar celdas de A a G en esa fila
                $sheet->mergeCells("A{$rowIndex}:G{$rowIndex}");
                // Aplicar estilo: fondo blanco, borde negro, texto centrado y en negrita
                $sheet->getStyle("A{$rowIndex}")->applyFromArray([
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D9D9D9'],
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'], // Color negro para los bordes
                        ],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'font' => [
                        'bold' => true,
                    ],
                ]);
            }
            
            
            // 2. Estilo para el cabezal de la tabla (encabezados de columna)
            // Se asume que la fila cuyo valor en A es "Fecha Movimiento" es el cabezal.
            if (strcasecmp($cellA, 'Fecha Movimiento') === 0) {
                $sheet->getStyle("A{$rowIndex}:G{$rowIndex}")->applyFromArray([
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D9D9D9'], // Color de fondo para el encabezado (gris claro)
                    ],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                    'font' => [
                        'bold' => true,
                    ],
                ]);
            }
            
            // 3. Estilos para los registros de movimientos
            // a) Filas de SALIDA (fondo rojo suave)
            if ($cellB === 'SALIDA') {
                $sheet->getStyle("A{$rowIndex}:G{$rowIndex}")->applyFromArray([
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FF9999'],
                    ],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                ]);
            }
            
            // b) Filas de ENTRADA y SALDO INICIAL (se tratará SALDO INICIAL igual que ENTRADA, fondo verde suave)
            if ($cellB === 'ENTRADA' || $cellB === 'SALDO INICIAL') {
                $sheet->getStyle("A{$rowIndex}:G{$rowIndex}")->applyFromArray([
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '99FF99'],
                    ],
                    'borders' => [
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
