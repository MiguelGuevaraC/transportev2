<?php 

namespace App\Exports;

use App\Models\CargaDocument;
use App\Models\DetailReception;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class KardexExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles
{
    protected $product_id;
    protected $from;
    protected $to;

    public function __construct($product_id = null, $from = null, $to = null)
    {

        $this->product_id = $product_id;
        $this->from = $from;
        $this->to = $to;
    }

    public function collection()
    {
        $queryCarga = CargaDocument::query()->whereNull('deleted_at');
        $queryRecep = DetailReception::query()->whereNull('deleted_at');
    
        if ($this->product_id != null) {
            $queryCarga->where('product_id', $this->product_id);
            $queryRecep->where('product_id', $this->product_id);
        } else {
            $queryRecep->whereNotNull('product_id');
        }
        if ($this->from) {
            $toDate = $this->to ?? now();
            
            $queryCarga->where('movement_date', '>=', $this->from)
                       ->where('movement_date', '<=', $toDate);
        
            $queryRecep->whereHas('reception.firstCarrierGuide', function ($query) use ($toDate) {
                $query->where('transferStartDate', '>=', $this->from)
                      ->where('transferStartDate', '<=', $toDate);
            });
        }
        

        
        $cargaDocuments = $queryCarga->orderBy('id', 'asc')->get()->map(function ($doc) {
            return [
                'id'            => $doc->id,
                'movement_date' => $doc->movement_date,
                'type'          => $doc->movement_type,
                'concept'       => $doc->product->description ?? "SIN DESCRIPCIÓN",
                'document'      => 'D' . str_pad($doc->product_id, 3, '0', STR_PAD_LEFT) . '-' . str_pad($doc->id, 8, '0', STR_PAD_LEFT),
                'quantity'      => $doc->quantity,
                'unit_price'    => $doc->unit_price,
                'total_cost'    => $doc->total_cost,
                'stock_before'  => $doc->stock_balance_before ?? 0,
                'stock_after'   => $doc->stock_balance_after ?? 0,
                'comment'       => $doc->comment ?? "-",
            ];
        });
    
        $detailReceptions = $queryRecep->whereHas('reception.firstCarrierGuide', function ($query) {
            $query->where('status_facturado', '!=', 'Anulada');
        })
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($detail) {
                return [
                    'id'            => "G-{$detail->id}",
                    'movement_date' => $detail->reception->firstCarrierGuide->transferStartDate,
                    'type'          => 'SALIDA',
                    'concept'       => 'GUIA TRANSPORTE',
                    'document'      => $detail->reception->firstCarrierGuide->numero ?? 'N/A',
                    'quantity'      => $detail->cant,
                    'unit_price'    => 0,
                    'total_cost'    => 0,
                    'stock_before'  => 0,
                    'stock_after'   => 0,
                    'comment'       => '-',
                ];
            });
        return (new Collection(array_merge($cargaDocuments->toArray(), $detailReceptions->toArray())))->sortBy('movement_date')->values();
    }

    public function headings(): array
    {
        return [
            ['REPORTE DE KARDEX'],
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
        static $saldo = 0;

        $entradaCantidad = $row['type'] === 'ENTRADA' ? $row['quantity'] : 0;
        $salidaCantidad  = $row['type'] === 'SALIDA' ? $row['quantity'] : 0;
        $saldo = ($saldo + $entradaCantidad - $salidaCantidad);

        return [
            (string) $row['movement_date'],
            (string) $row['type'],
            (string) $row['concept'],
            (string) $row['document'],
            (string) $entradaCantidad,
            (string) $salidaCantidad,
            (string) $saldo,
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
        if ($highestRow < 5) {
            return [];
        }
    
        foreach ($sheet->getRowIterator(5) as $row) {
            $cell = 'B' . $row->getRowIndex();
            $type = $sheet->getCell($cell)->getValue();
            $color = $type === 'SALIDA' ? 'FF9999' : ($type === 'ENTRADA' ? '99FF99' : null);
            if ($color) {
                $sheet->getStyle("A{$row->getRowIndex()}:H{$row->getRowIndex()}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $color]
                    ]
                ]);
            }
        }
    
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true]],
            4 => ['font' => ['bold' => true, 'size' => 12], 'alignment' => ['horizontal' => 'center']],
        ];
    }
    
}